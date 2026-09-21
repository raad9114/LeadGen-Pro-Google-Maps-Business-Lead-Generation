<!-- Exports Page -->
<div class="content-card">
    <div class="card-header"><h6><i class="bi bi-download me-2"></i>Export Leads</h6></div>
    <div class="card-body">
        <p class="text-muted mb-4">Export your leads to CSV or XLSX format. You can export all leads or apply filters first.</p>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Category Filter</label>
                <select class="form-select" id="exportCategory">
                    <option value="">All Categories</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Status Filter</label>
                <select class="form-select" id="exportStatus">
                    <option value="">All Statuses</option>
                    <option value="new">New</option>
                    <option value="contacted">Contacted</option>
                    <option value="follow_up">Follow Up</option>
                    <option value="interested">Interested</option>
                    <option value="converted">Converted</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Contact Filter</label>
                <select class="form-select" id="exportContact">
                    <option value="">Any</option>
                    <option value="has_phone">Has Phone</option>
                    <option value="has_email">Has Email</option>
                    <option value="has_website">Has Website</option>
                </select>
            </div>
        </div>

        <div class="d-flex gap-3">
            <button class="btn btn-primary" onclick="doExport('csv')"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Export as CSV</button>
            <button class="btn btn-success" onclick="doExport('xlsx')"><i class="bi bi-file-earmark-excel me-2"></i>Export as XLSX</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    fetch(APP_URL + '/api/categories', { headers: {'X-CSRF-TOKEN': CSRF_TOKEN} })
        .then(r => r.json()).then(res => {
            if (res.success && res.data) {
                res.data.forEach(c => {
                    document.getElementById('exportCategory').innerHTML += `<option value="${escHtml(c.name)}">${escHtml(c.name)}</option>`;
                });
            }
        });
});

function doExport(format) {
    const filters = {};
    const cat = document.getElementById('exportCategory').value;
    const status = document.getElementById('exportStatus').value;
    const contact = document.getElementById('exportContact').value;
    if (cat) filters.category = cat;
    if (status) filters.lead_status = status;
    if (contact) filters[contact] = '1';

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = APP_URL + '/api/export/' + format;
    form.innerHTML = `<input type="hidden" name="_csrf_token" value="${CSRF_TOKEN}">`;
    Object.entries(filters).forEach(([k,v]) => {
        form.innerHTML += `<input type="hidden" name="filters[${k}]" value="${escHtml(v)}">`;
    });
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
</script>
