<!-- API Usage Page -->
<div class="row g-4 mb-4">
    <div class="col-md-3"><div class="stat-card"><div class="stat-icon primary"><i class="bi bi-lightning"></i></div><div class="stat-value"><?= number_format($stats['today'] ?? 0) ?></div><div class="stat-label">Requests Today</div></div></div>
    <div class="col-md-3"><div class="stat-card"><div class="stat-icon info"><i class="bi bi-calendar3"></i></div><div class="stat-value"><?= number_format($stats['this_month'] ?? 0) ?></div><div class="stat-label">This Month</div></div></div>
    <div class="col-md-3"><div class="stat-card"><div class="stat-icon success"><i class="bi bi-check-circle"></i></div><div class="stat-value"><?= number_format($stats['successful'] ?? 0) ?></div><div class="stat-label">Successful Today</div></div></div>
    <div class="col-md-3"><div class="stat-card"><div class="stat-icon danger"><i class="bi bi-x-circle"></i></div><div class="stat-value"><?= number_format($stats['failed'] ?? 0) ?></div><div class="stat-label">Failed Today</div></div></div>
</div>

<div class="content-card">
    <div class="card-header"><h6><i class="bi bi-activity me-2"></i>Recent API Requests</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive" style="max-height:600px;overflow-y:auto;">
            <table class="table">
                <thead><tr><th>Endpoint</th><th>HTTP</th><th>Result</th><th>Time (ms)</th><th>Error</th><th>Date</th></tr></thead>
                <tbody>
                    <?php foreach ($recentLogs as $log): ?>
                    <tr>
                        <td><code style="font-size:11px;"><?= Validator::e($log['endpoint']) ?></code></td>
                        <td><span class="badge bg-<?= ($log['http_status'] ?? 0) < 400 ? 'success' : 'danger' ?>"><?= $log['http_status'] ?? '—' ?></span></td>
                        <td><span class="badge bg-<?= $log['request_result'] === 'success' ? 'success' : 'danger' ?>"><?= $log['request_result'] ?></span></td>
                        <td><?= $log['response_time_ms'] ?? '—' ?></td>
                        <td><small class="text-danger"><?= Validator::e($log['error_message'] ?? '') ?></small></td>
                        <td><small class="text-muted"><?= date('M d, H:i:s', strtotime($log['created_at'])) ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recentLogs)): ?>
                    <tr><td colspan="6"><div class="empty-state py-4"><p>No API requests logged yet</p></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
