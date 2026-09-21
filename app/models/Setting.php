<?php
/**
 * Setting Model
 */

class Setting
{
    private PDO $db;
    private static array $cache = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function get(string $key, ?string $default = null): ?string
    {
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        $stmt = $this->db->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();

        if ($value === false) {
            return $default;
        }

        self::$cache[$key] = $value;
        return $value;
    }

    public function set(string $key, ?string $value, string $group = 'general'): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO settings (setting_key, setting_value, setting_group)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        );
        $stmt->execute([$key, $value, $group]);
        self::$cache[$key] = $value;
    }

    public function getAll(): array
    {
        return $this->db->query('SELECT * FROM settings ORDER BY setting_group, setting_key')->fetchAll();
    }

    public function getByGroup(string $group): array
    {
        $stmt = $this->db->prepare('SELECT * FROM settings WHERE setting_group = ? ORDER BY setting_key');
        $stmt->execute([$group]);
        return $stmt->fetchAll();
    }

    public function saveMultiple(array $settings, string $group = 'general'): void
    {
        foreach ($settings as $key => $value) {
            $this->set($key, $value, $group);
        }
    }
}
