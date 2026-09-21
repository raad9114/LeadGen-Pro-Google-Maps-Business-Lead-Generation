<?php
/**
 * SearchJob Model
 */

class SearchJob
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO search_jobs (user_id, category_name, country_name, city_name, area_name,
                search_query, search_mode, latitude, longitude, radius_km, max_results, status, started_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
        );
        $stmt->execute([
            $data['user_id'],
            $data['category_name'],
            $data['country_name'] ?? null,
            $data['city_name'] ?? null,
            $data['area_name'] ?? null,
            $data['search_query'],
            $data['search_mode'] ?? 'text',
            $data['latitude'] ?? null,
            $data['longitude'] ?? null,
            $data['radius_km'] ?? null,
            $data['max_results'] ?? 60,
            'searching',
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM search_jobs WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function updateStatus(int $id, string $status, array $stats = []): void
    {
        $fields = ['status = ?'];
        $values = [$status];

        foreach (['total_found', 'new_leads', 'duplicates', 'emails_found'] as $stat) {
            if (isset($stats[$stat])) {
                $fields[] = "{$stat} = ?";
                $values[] = $stats[$stat];
            }
        }

        if (isset($stats['error_message'])) {
            $fields[] = 'error_message = ?';
            $values[] = $stats['error_message'];
        }

        if (in_array($status, ['completed', 'failed'])) {
            $fields[] = 'completed_at = NOW()';
        }

        $values[] = $id;
        $stmt = $this->db->prepare('UPDATE search_jobs SET ' . implode(', ', $fields) . ' WHERE id = ?');
        $stmt->execute($values);
    }

    public function getRecent(int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            'SELECT sj.*, u.username FROM search_jobs sj
             LEFT JOIN users u ON sj.user_id = u.id
             ORDER BY sj.created_at DESC LIMIT ?'
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function getAll(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;

        $total = (int) $this->db->query('SELECT COUNT(*) FROM search_jobs')->fetchColumn();

        $stmt = $this->db->prepare(
            "SELECT sj.*, u.username FROM search_jobs sj
             LEFT JOIN users u ON sj.user_id = u.id
             ORDER BY sj.created_at DESC LIMIT {$perPage} OFFSET {$offset}"
        );
        $stmt->execute();

        return ['data' => $stmt->fetchAll(), 'total' => $total];
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM search_jobs WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function getTodayCount(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM search_jobs WHERE DATE(created_at) = CURDATE()"
        )->fetchColumn();
    }
}
