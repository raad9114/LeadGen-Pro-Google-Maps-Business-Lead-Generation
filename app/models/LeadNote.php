<?php
/**
 * LeadNote Model
 */

class LeadNote
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getByLeadId(int $leadId): array
    {
        $stmt = $this->db->prepare(
            'SELECT ln.*, u.username, u.full_name FROM lead_notes ln
             LEFT JOIN users u ON ln.user_id = u.id
             WHERE ln.lead_id = ? ORDER BY ln.created_at DESC'
        );
        $stmt->execute([$leadId]);
        return $stmt->fetchAll();
    }

    public function create(int $leadId, int $userId, string $note): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO lead_notes (lead_id, user_id, note) VALUES (?, ?, ?)'
        );
        $stmt->execute([$leadId, $userId, $note]);
        return (int) $this->db->lastInsertId();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM lead_notes WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
