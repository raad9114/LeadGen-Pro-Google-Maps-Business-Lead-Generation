<?php
/**
 * Category Model
 */

class Category
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(bool $activeOnly = true): array
    {
        $where = $activeOnly ? 'WHERE is_active = 1' : '';
        return $this->db->query("SELECT * FROM business_categories {$where} ORDER BY sort_order ASC, name ASC")->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM business_categories WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $slug = $this->slugify($data['name']);
        $stmt = $this->db->prepare(
            'INSERT INTO business_categories (name, slug, icon, variants, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['name'],
            $slug,
            $data['icon'] ?? 'bi-building',
            $data['variants'] ?? null,
            $data['is_active'] ?? 1,
            $data['sort_order'] ?? 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $values = [];

        if (isset($data['name'])) {
            $fields[] = 'name = ?';
            $values[] = $data['name'];
            $fields[] = 'slug = ?';
            $values[] = $this->slugify($data['name']);
        }
        if (isset($data['icon'])) {
            $fields[] = 'icon = ?';
            $values[] = $data['icon'];
        }
        if (isset($data['variants'])) {
            $fields[] = 'variants = ?';
            $values[] = $data['variants'];
        }
        if (isset($data['is_active'])) {
            $fields[] = 'is_active = ?';
            $values[] = $data['is_active'];
        }
        if (isset($data['sort_order'])) {
            $fields[] = 'sort_order = ?';
            $values[] = $data['sort_order'];
        }

        if (empty($fields)) return false;

        $values[] = $id;
        $stmt = $this->db->prepare('UPDATE business_categories SET ' . implode(', ', $fields) . ' WHERE id = ?');
        return $stmt->execute($values);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM business_categories WHERE id = ?');
        return $stmt->execute([$id]);
    }

    private function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9-]/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        return trim($text, '-');
    }
}
