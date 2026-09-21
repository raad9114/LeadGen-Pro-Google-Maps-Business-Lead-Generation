<?php
/**
 * LeadActivity Model
 */

class LeadActivity
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function log(int $leadId, ?int $userId, string $action, ?string $details = null): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO lead_activities (lead_id, user_id, action, details) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$leadId, $userId, $action, $details]);
        return (int) $this->db->lastInsertId();
    }

    public function getByLeadId(int $leadId, int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            'SELECT la.*, u.username FROM lead_activities la
             LEFT JOIN users u ON la.user_id = u.id
             WHERE la.lead_id = ? ORDER BY la.created_at DESC LIMIT ?'
        );
        $stmt->execute([$leadId, $limit]);
        return $stmt->fetchAll();
    }
}
