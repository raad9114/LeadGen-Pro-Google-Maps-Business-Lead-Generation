<?php
/**
 * LeadEmail Model
 */

class LeadEmail
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getByLeadId(int $leadId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM lead_emails WHERE lead_id = ? ORDER BY is_primary DESC, created_at ASC');
        $stmt->execute([$leadId]);
        return $stmt->fetchAll();
    }

    public function create(int $leadId, string $email, ?string $sourceUrl = null, bool $isPrimary = false): int
    {
        // Check if this email already exists for this lead
        $stmt = $this->db->prepare('SELECT id FROM lead_emails WHERE lead_id = ? AND email = ?');
        $stmt->execute([$leadId, $email]);
        if ($stmt->fetch()) {
            return 0; // Already exists
        }

        $stmt = $this->db->prepare(
            'INSERT INTO lead_emails (lead_id, email, source_url, is_primary, status) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$leadId, $email, $sourceUrl, $isPrimary ? 1 : 0, 'found']);
        return (int) $this->db->lastInsertId();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM lead_emails WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
