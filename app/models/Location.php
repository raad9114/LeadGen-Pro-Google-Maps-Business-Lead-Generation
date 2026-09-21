<?php
/**
 * Location Model (Countries, Cities, Areas)
 */

class Location
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ─── Countries ─────────────────────────────────
    public function getCountries(bool $activeOnly = true): array
    {
        $where = $activeOnly ? 'WHERE is_active = 1' : '';
        return $this->db->query("SELECT * FROM countries {$where} ORDER BY name ASC")->fetchAll();
    }

    public function findCountryById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM countries WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function createCountry(string $name, string $code): int
    {
        $stmt = $this->db->prepare('INSERT INTO countries (name, code) VALUES (?, ?)');
        $stmt->execute([$name, strtoupper($code)]);
        return (int) $this->db->lastInsertId();
    }

    public function deleteCountry(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM countries WHERE id = ?');
        return $stmt->execute([$id]);
    }

    // ─── Cities ────────────────────────────────────
    public function getCities(int $countryId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM cities WHERE country_id = ? AND is_active = 1 ORDER BY name ASC');
        $stmt->execute([$countryId]);
        return $stmt->fetchAll();
    }

    public function findCityById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM cities WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function createCity(int $countryId, string $name, ?float $lat = null, ?float $lng = null): int
    {
        $stmt = $this->db->prepare('INSERT INTO cities (country_id, name, latitude, longitude) VALUES (?, ?, ?, ?)');
        $stmt->execute([$countryId, $name, $lat, $lng]);
        return (int) $this->db->lastInsertId();
    }

    public function deleteCity(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM cities WHERE id = ?');
        return $stmt->execute([$id]);
    }

    // ─── Areas ─────────────────────────────────────
    public function getAreas(int $cityId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM areas WHERE city_id = ? AND is_active = 1 ORDER BY name ASC');
        $stmt->execute([$cityId]);
        return $stmt->fetchAll();
    }

    public function findAreaById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM areas WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function createArea(int $cityId, string $name, ?float $lat = null, ?float $lng = null): int
    {
        $stmt = $this->db->prepare('INSERT INTO areas (city_id, name, latitude, longitude) VALUES (?, ?, ?, ?)');
        $stmt->execute([$cityId, $name, $lat, $lng]);
        return (int) $this->db->lastInsertId();
    }

    public function deleteArea(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM areas WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function getAllCities(): array
    {
        return $this->db->query(
            'SELECT c.*, co.name as country_name FROM cities c
             JOIN countries co ON c.country_id = co.id
             ORDER BY co.name, c.name'
        )->fetchAll();
    }

    public function getAllAreas(): array
    {
        return $this->db->query(
            'SELECT a.*, ci.name as city_name, co.name as country_name FROM areas a
             JOIN cities ci ON a.city_id = ci.id
             JOIN countries co ON ci.country_id = co.id
             ORDER BY co.name, ci.name, a.name'
        )->fetchAll();
    }
}
