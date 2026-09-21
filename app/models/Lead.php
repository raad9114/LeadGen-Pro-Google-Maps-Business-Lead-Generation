<?php
/**
 * Lead Model
 */

class Lead
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM leads WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByPlaceId(string $placeId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM leads WHERE place_id = ?');
        $stmt->execute([$placeId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Insert or update lead (upsert by place_id)
     */
    public function upsert(array $data): array
    {
        $existing = $this->findByPlaceId($data['place_id']);

        if ($existing) {
            // Update existing — fill in missing fields only
            $updates = [];
            $values = [];
            $fieldsToUpdate = [
                'business_name', 'primary_category', 'types_json',
                'formatted_address', 'national_phone', 'international_phone',
                'website_url', 'google_maps_url', 'latitude', 'longitude',
                'rating', 'review_count', 'business_status',
            ];

            foreach ($fieldsToUpdate as $field) {
                if (isset($data[$field]) && $data[$field] !== null &&
                    ($existing[$field] === null || $existing[$field] === '')) {
                    $updates[] = "{$field} = ?";
                    $values[] = $data[$field];
                } elseif (isset($data[$field]) && $data[$field] !== null) {
                    // Update with latest data from API
                    $updates[] = "{$field} = ?";
                    $values[] = $data[$field];
                }
            }

            $updates[] = 'last_api_refresh = NOW()';

            if (!empty($updates)) {
                $values[] = $existing['id'];
                $stmt = $this->db->prepare(
                    'UPDATE leads SET ' . implode(', ', $updates) . ' WHERE id = ?'
                );
                $stmt->execute($values);
            }

            return ['id' => $existing['id'], 'is_new' => false];
        }

        // Insert new lead
        $stmt = $this->db->prepare(
            'INSERT INTO leads (place_id, business_name, primary_category, types_json,
                formatted_address, national_phone, international_phone, email,
                website_url, google_maps_url, latitude, longitude,
                rating, review_count, business_status,
                search_category, search_country, search_city, search_area,
                search_query, search_job_id, lead_status, last_api_refresh)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
        );
        $stmt->execute([
            $data['place_id'],
            $data['business_name'],
            $data['primary_category'] ?? null,
            $data['types_json'] ?? null,
            $data['formatted_address'] ?? null,
            $data['national_phone'] ?? null,
            $data['international_phone'] ?? null,
            $data['email'] ?? null,
            $data['website_url'] ?? null,
            $data['google_maps_url'] ?? null,
            $data['latitude'] ?? null,
            $data['longitude'] ?? null,
            $data['rating'] ?? null,
            $data['review_count'] ?? null,
            $data['business_status'] ?? null,
            $data['search_category'] ?? null,
            $data['search_country'] ?? null,
            $data['search_city'] ?? null,
            $data['search_area'] ?? null,
            $data['search_query'] ?? null,
            $data['search_job_id'] ?? null,
            'new',
        ]);

        return ['id' => (int) $this->db->lastInsertId(), 'is_new' => true];
    }

    /**
     * List leads with filters and pagination
     */
    public function list(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = '(business_name LIKE ? OR formatted_address LIKE ? OR national_phone LIKE ? OR email LIKE ?)';
            $search = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$search, $search, $search, $search]);
        }
        if (!empty($filters['category'])) {
            $where[] = 'search_category = ?';
            $params[] = $filters['category'];
        }
        if (!empty($filters['country'])) {
            $where[] = 'search_country = ?';
            $params[] = $filters['country'];
        }
        if (!empty($filters['city'])) {
            $where[] = 'search_city = ?';
            $params[] = $filters['city'];
        }
        if (!empty($filters['area'])) {
            $where[] = 'search_area = ?';
            $params[] = $filters['area'];
        }
        if (!empty($filters['lead_status'])) {
            $where[] = 'lead_status = ?';
            $params[] = $filters['lead_status'];
        }
        if (!empty($filters['has_phone'])) {
            $where[] = 'national_phone IS NOT NULL AND national_phone != ""';
        }
        if (!empty($filters['no_phone'])) {
            $where[] = '(national_phone IS NULL OR national_phone = "")';
        }
        if (!empty($filters['has_email'])) {
            $where[] = 'email IS NOT NULL AND email != ""';
        }
        if (!empty($filters['no_email'])) {
            $where[] = '(email IS NULL OR email = "")';
        }
        if (!empty($filters['has_website'])) {
            $where[] = 'website_url IS NOT NULL AND website_url != ""';
        }
        if (!empty($filters['no_website'])) {
            $where[] = '(website_url IS NULL OR website_url = "")';
        }
        if (!empty($filters['min_rating'])) {
            $where[] = 'rating >= ?';
            $params[] = (float) $filters['min_rating'];
        }
        if (!empty($filters['min_reviews'])) {
            $where[] = 'review_count >= ?';
            $params[] = (int) $filters['min_reviews'];
        }
        if (!empty($filters['search_job_id'])) {
            $where[] = 'search_job_id = ?';
            $params[] = (int) $filters['search_job_id'];
        }
        if (!empty($filters['date_from'])) {
            $where[] = 'created_at >= ?';
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $where[] = 'created_at <= ?';
            $params[] = $filters['date_to'] . ' 23:59:59';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Count
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM leads {$whereClause}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        // Data
        $offset = ($page - 1) * $perPage;
        $dataStmt = $this->db->prepare(
            "SELECT * FROM leads {$whereClause} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}"
        );
        $dataStmt->execute($params);
        $data = $dataStmt->fetchAll();

        return ['data' => $data, 'total' => $total];
    }

    /**
     * Update lead status
     */
    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare('UPDATE leads SET lead_status = ? WHERE id = ?');
        return $stmt->execute([$status, $id]);
    }

    /**
     * Update lead fields
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $values = [];

        $allowed = [
            'business_name', 'primary_category', 'formatted_address',
            'national_phone', 'international_phone', 'email', 'email_source_url',
            'website_url', 'lead_status', 'rating', 'review_count',
        ];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($fields)) return false;

        $values[] = $id;
        $stmt = $this->db->prepare('UPDATE leads SET ' . implode(', ', $fields) . ' WHERE id = ?');
        return $stmt->execute($values);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM leads WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function bulkDelete(array $ids): int
    {
        if (empty($ids)) return 0;
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("DELETE FROM leads WHERE id IN ({$placeholders})");
        $stmt->execute($ids);
        return $stmt->rowCount();
    }

    public function bulkUpdateStatus(array $ids, string $status): int
    {
        if (empty($ids)) return 0;
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $params = array_merge([$status], $ids);
        $stmt = $this->db->prepare("UPDATE leads SET lead_status = ? WHERE id IN ({$placeholders})");
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Dashboard statistics
     */
    public function getStats(): array
    {
        $stats = [];
        $stats['total'] = (int) $this->db->query('SELECT COUNT(*) FROM leads')->fetchColumn();
        $stats['new'] = (int) $this->db->query("SELECT COUNT(*) FROM leads WHERE lead_status = 'new'")->fetchColumn();
        $stats['with_phone'] = (int) $this->db->query("SELECT COUNT(*) FROM leads WHERE national_phone IS NOT NULL AND national_phone != ''")->fetchColumn();
        $stats['with_email'] = (int) $this->db->query("SELECT COUNT(*) FROM leads WHERE email IS NOT NULL AND email != ''")->fetchColumn();
        $stats['with_website'] = (int) $this->db->query("SELECT COUNT(*) FROM leads WHERE website_url IS NOT NULL AND website_url != ''")->fetchColumn();
        $stats['converted'] = (int) $this->db->query("SELECT COUNT(*) FROM leads WHERE lead_status = 'converted'")->fetchColumn();
        return $stats;
    }

    /**
     * Get leads by category (for dashboard chart)
     */
    public function getByCategory(int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            'SELECT search_category, COUNT(*) as count FROM leads
             WHERE search_category IS NOT NULL
             GROUP BY search_category ORDER BY count DESC LIMIT ?'
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get leads by area (for dashboard chart)
     */
    public function getByArea(int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            'SELECT search_area, COUNT(*) as count FROM leads
             WHERE search_area IS NOT NULL
             GROUP BY search_area ORDER BY count DESC LIMIT ?'
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get recent leads
     */
    public function getRecent(int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, business_name, search_category, national_phone, email, search_area, lead_status, created_at
             FROM leads ORDER BY created_at DESC LIMIT ?'
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get leads without emails that have websites
     */
    public function getLeadsNeedingEmails(int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, place_id, business_name, website_url FROM leads
             WHERE website_url IS NOT NULL AND website_url != ''
             AND (email IS NULL OR email = '')
             ORDER BY created_at DESC LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get leads for export with optional filtering
     */
    public function getForExport(array $ids = [], array $filters = []): array
    {
        if (!empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $this->db->prepare("SELECT * FROM leads WHERE id IN ({$placeholders}) ORDER BY created_at DESC");
            $stmt->execute($ids);
            return $stmt->fetchAll();
        }

        $result = $this->list($filters, 1, 100000);
        return $result['data'];
    }
}
