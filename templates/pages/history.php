<!-- Search History Page -->
<div class="content-card">
    <div class="card-header">
        <h6><i class="bi bi-clock-history me-2"></i>Search History</h6>
        <a href="<?= Config::appUrl() ?>/search" class="btn btn-sm btn-primary"><i class="bi bi-plus me-1"></i>New Search</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table" id="historyTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Category</th>
                        <th>Location</th>
                        <th>Results</th>
                        <th>New</th>
                        <th>Emails</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="historyBody">
                    <tr><td colspan="9"><div class="empty-state py-4"><p>Loading...</p></div></td></tr>
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center p-3">
            <small class="text-muted" id="historyInfo"></small>
            <nav><ul class="pagination pagination-sm mb-0" id="historyPagination"></ul></nav>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => loadHistory(1));

function loadHistory(page) {
    fetch(`${APP_URL}/api/search-history?page=${page}&per_page=20`, {
        headers: { 'X-CSRF-TOKEN': CSRF_TOKEN }
    })
    .then(r => r.json())
    .then(res => {
        const tbody = document.getElementById('historyBody');
        const data = res.data || [];

        if (!data.length) {
            tbody.innerHTML = '<tr><td colspan="9"><div class="empty-state py-4"><i class="bi bi-clock-history"></i><h5>No searches yet</h5></div></td></tr>';
            return;
        }

        tbody.innerHTML = data.map(s => `
            <tr>
                <td><small class="fw-semibold">#${s.id}</small></td>
                <td><strong>${escHtml(s.category_name)}</strong></td>
                <td><small>${escHtml([s.area_name,s.city_name,s.country_name].filter(Boolean).join(', '))}</small></td>
                <td><span class="fw-bold">${s.total_found}</span></td>
                <td><span class="text-success fw-semibold">${s.new_leads}</span></td>
                <td><span class="text-warning fw-semibold">${s.emails_found}</span></td>
                <td><span class="badge-status badge-job-${s.status}">${s.status.replace(/_/g,' ')}</span></td>
                <td><small class="text-muted">${new Date(s.created_at).toLocaleString()}</small></td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="${APP_URL}/leads?search_job_id=${s.id}" class="btn btn-icon btn-sm btn-outline-primary" title="View Results"><i class="bi bi-eye"></i></a>
                        <button class="btn btn-icon btn-sm btn-outline-danger" title="Delete" onclick="deleteSearch(${s.id})"><i class="bi bi-trash"></i></button>
                    </div>
                </td>
            </tr>
        `).join('');

        const total = res.pagination?.total || 0;
        document.getElementById('historyInfo').textContent = `${total} total searches`;
    });
}

function deleteSearch(id) {
    if (!confirm('Delete this search record?')) return;
    apiDelete('/api/search-history/' + id).then(res => {
        showToast(res.message, res.success ? 'success' : 'error');
        loadHistory(1);
    });
}
</script>
