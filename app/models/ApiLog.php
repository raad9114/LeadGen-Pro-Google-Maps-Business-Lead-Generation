<?php
/**
 * ApiLog Model
 */

class ApiLog
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function log(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO api_usage_logs (endpoint, search_job_id, place_id, http_status,
                response_time_ms, request_result, error_message)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['endpoint'],
            $data['search_job_id'] ?? null,
            $data['place_id'] ?? null,
            $data['http_status'] ?? null,
            $data['response_time_ms'] ?? null,
            $data['request_result'] ?? 'success',
            $data['error_message'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getStats(): array
    {
        $stats = [];
        $stats['today'] = (int) $this->db->query(
            "SELECT COUNT(*) FROM api_usage_logs WHERE DATE(created_at) = CURDATE()"
        )->fetchColumn();
        $stats['this_month'] = (int) $this->db->query(
            "SELECT COUNT(*) FROM api_usage_logs WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())"
        )->fetchColumn();
        $stats['successful'] = (int) $this->db->query(
            "SELECT COUNT(*) FROM api_usage_logs WHERE request_result = 'success' AND DATE(created_at) = CURDATE()"
        )->fetchColumn();
        $stats['failed'] = (int) $this->db->query(
            "SELECT COUNT(*) FROM api_usage_logs WHERE request_result != 'success' AND DATE(created_at) = CURDATE()"
        )->fetchColumn();
        return $stats;
    }

    public function getRecent(int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM api_usage_logs ORDER BY created_at DESC LIMIT ?'
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function getByJob(int $jobId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM api_usage_logs WHERE search_job_id = ? ORDER BY created_at ASC');
        $stmt->execute([$jobId]);
        return $stmt->fetchAll();
    }
}
