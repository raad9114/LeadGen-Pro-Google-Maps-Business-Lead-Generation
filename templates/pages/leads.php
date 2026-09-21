<!-- All Leads Page -->
<div class="row g-4">
    <!-- Filters Sidebar -->
    <div class="col-xl-3">
        <div class="filters-panel">
            <h6 class="fw-bold mb-3"><i class="bi bi-funnel me-2"></i>Filters</h6>

            <div class="filter-group">
                <label>Search</label>
                <input type="text" class="form-control form-control-sm" id="filterSearch" placeholder="Name, phone, email...">
            </div>
            <div class="filter-group">
                <label>Category</label>
                <select class="form-select form-select-sm" id="filterCategory">
                    <option value="">All Categories</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Lead Status</label>
                <select class="form-select form-select-sm" id="filterStatus">
                    <option value="">All Statuses</option>
                    <option value="new">New</option>
                    <option value="not_contacted">Not Contacted</option>
                    <option value="contacted">Contacted</option>
                    <option value="follow_up">Follow Up</option>
                    <option value="interested">Interested</option>
                    <option value="converted">Converted</option>
                    <option value="not_interested">Not Interested</option>
                    <option value="invalid">Invalid</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Contact Info</label>
                <div class="form-check"><input class="form-check-input" type="checkbox" id="filterHasPhone"><label class="form-check-label small">Has Phone</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" id="filterHasEmail"><label class="form-check-label small">Has Email</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" id="filterHasWebsite"><label class="form-check-label small">Has Website</label></div>
            </div>
            <div class="filter-group">
                <label>Min Rating</label>
                <select class="form-select form-select-sm" id="filterMinRating">
                    <option value="">Any</option>
                    <option value="3">3+</option>
                    <option value="3.5">3.5+</option>
                    <option value="4">4+</option>
                    <option value="4.5">4.5+</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Min Reviews</label>
                <input type="number" class="form-control form-control-sm" id="filterMinReviews" placeholder="e.g. 50">
            </div>
            <div class="filter-group">
                <label>Date From</label>
                <input type="date" class="form-control form-control-sm" id="filterDateFrom">
            </div>
            <div class="filter-group">
                <label>Date To</label>
                <input type="date" class="form-control form-control-sm" id="filterDateTo">
            </div>

            <button class="btn btn-primary btn-sm w-100 mt-2" onclick="loadLeads(1)">
                <i class="bi bi-funnel me-1"></i>Apply Filters
            </button>
            <button class="btn btn-outline-secondary btn-sm w-100 mt-2" onclick="clearFilters()">Clear</button>
        </div>
    </div>

    <!-- Leads Table -->
    <div class="col-xl-9">
        <!-- Bulk Actions Bar -->
        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
            <div class="d-flex gap-2">
                <select class="form-select form-select-sm" id="bulkAction" style="width:auto;">
                    <option value="">Bulk Actions...</option>
                    <option value="status">Change Status</option>
                    <option value="email">Find Emails</option>
                    <option value="export-csv">Export CSV</option>
                    <option value="export-xlsx">Export XLSX</option>
                    <option value="delete">Delete</option>
                </select>
                <select class="form-select form-select-sm" id="bulkStatusValue" style="width:auto; display:none;">
                    <option value="new">New</option>
                    <option value="not_contacted">Not Contacted</option>
                    <option value="contacted">Contacted</option>
                    <option value="follow_up">Follow Up</option>
                    <option value="interested">Interested</option>
                    <option value="converted">Converted</option>
                    <option value="not_interested">Not Interested</option>
                </select>
                <button class="btn btn-sm btn-primary" onclick="executeBulk()">Apply</button>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-primary" onclick="exportFiltered('csv')"><i class="bi bi-file-spreadsheet me-1"></i>Export CSV</button>
                <button class="btn btn-sm btn-outline-success" onclick="exportFiltered('xlsx')"><i class="bi bi-file-excel me-1"></i>Export XLSX</button>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-secondary active" id="viewTable" onclick="switchView('table')"><i class="bi bi-table"></i></button>
                    <button class="btn btn-outline-secondary" id="viewMap" onclick="switchView('map')"><i class="bi bi-pin-map"></i></button>
                </div>
            </div>
        </div>

        <!-- Table View -->
        <div class="table-card" id="tableView">
            <div class="table-responsive">
                <table class="table" id="leadsTable">
                    <thead>
                        <tr>
                            <th><input type="checkbox" class="form-check-input" id="selectAllLeads"></th>
                            <th>Business Name</th>
                            <th>Category</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Rating</th>
                            <th>Area</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="leadsBody">
                        <tr><td colspan="10"><div class="empty-state py-4"><i class="bi bi-inbox"></i><h5>Loading leads...</h5></div></td></tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex align-items-center justify-content-between p-3 border-top">
                <small class="text-muted" id="leadsPaginationInfo">Showing 0 leads</small>
                <nav><ul class="pagination pagination-sm mb-0" id="leadsPagination"></ul></nav>
            </div>
        </div>

        <!-- Map View -->
        <div id="mapViewContainer" style="display:none;">
            <div id="map-view"></div>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
const perPage = 25;
let currentLeads = [];

document.addEventListener('DOMContentLoaded', function() {
    // Check URL params for search_job_id
    const params = new URLSearchParams(window.location.search);
    const jobId = params.get('search_job_id');

    loadLeads(1);

    // Load categories for filter
    fetch(APP_URL + '/api/categories', { headers: { 'X-CSRF-TOKEN': CSRF_TOKEN } })
        .then(r => r.json())
        .then(res => {
            if (res.success && res.data) {
                const sel = document.getElementById('filterCategory');
                res.data.forEach(c => {
                    sel.innerHTML += `<option value="${escHtml(c.name)}">${escHtml(c.name)}</option>`;
                });
            }
        });

    document.getElementById('selectAllLeads').addEventListener('change', function() {
        document.querySelectorAll('#leadsBody .row-check').forEach(cb => cb.checked = this.checked);
    });

    document.getElementById('bulkAction').addEventListener('change', function() {
        document.getElementById('bulkStatusValue').style.display = this.value === 'status' ? 'inline-block' : 'none';
    });

    // Debounced search
    let searchTimeout;
    document.getElementById('filterSearch').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => loadLeads(1), 500);
    });
});

function getFilters() {
    const f = {};
    const search = document.getElementById('filterSearch').value;
    if (search) f.search = search;

    const category = document.getElementById('filterCategory').value;
    if (category) f.category = category;

    const status = document.getElementById('filterStatus').value;
    if (status) f.lead_status = status;

    if (document.getElementById('filterHasPhone').checked) f.has_phone = '1';
    if (document.getElementById('filterHasEmail').checked) f.has_email = '1';
    if (document.getElementById('filterHasWebsite').checked) f.has_website = '1';

    const minRating = document.getElementById('filterMinRating').value;
    if (minRating) f.min_rating = minRating;

    const minReviews = document.getElementById('filterMinReviews').value;
    if (minReviews) f.min_reviews = minReviews;

    const dateFrom = document.getElementById('filterDateFrom').value;
    if (dateFrom) f.date_from = dateFrom;

    const dateTo = document.getElementById('filterDateTo').value;
    if (dateTo) f.date_to = dateTo;

    const params = new URLSearchParams(window.location.search);
    const jobId = params.get('search_job_id');
    if (jobId) f.search_job_id = jobId;

    return f;
}

function loadLeads(page) {
    currentPage = page;
    const filters = getFilters();

    let url = `${APP_URL}/api/leads?page=${page}&per_page=${perPage}`;
    Object.entries(filters).forEach(([k, v]) => { url += `&${k}=${encodeURIComponent(v)}`; });

    fetch(url, { headers: { 'X-CSRF-TOKEN': CSRF_TOKEN } })
        .then(r => r.json())
        .then(res => {
            currentLeads = res.data || [];
            renderLeadsTable(currentLeads);

            const total = res.pagination?.total || 0;
            const totalPages = res.pagination?.total_pages || 1;
            document.getElementById('leadsPaginationInfo').textContent = `Showing ${currentLeads.length} of ${total} leads`;
            renderPagination(totalPages, page);
        });
}

function renderLeadsTable(leads) {
    const tbody = document.getElementById('leadsBody');

    if (leads.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10"><div class="empty-state py-4"><i class="bi bi-inbox"></i><h5>No leads found</h5><p>Try adjusting your filters or run a new search</p></div></td></tr>';
        return;
    }

    tbody.innerHTML = leads.map(l => `
        <tr>
            <td><input type="checkbox" class="form-check-input row-check" value="${l.id}"></td>
            <td>
                <a href="${APP_URL}/leads/${l.id}" class="text-decoration-none fw-semibold">${escHtml(l.business_name)}</a>
                <br><small class="text-muted text-truncate-2" style="max-width:200px;">${escHtml(l.formatted_address || '')}</small>
            </td>
            <td><small>${escHtml(l.search_category || l.primary_category || '—')}</small></td>
            <td><small>${escHtml(l.national_phone || '—')}</small></td>
            <td><small>${l.email ? '<a href="mailto:'+escHtml(l.email)+'">'+escHtml(l.email)+'</a>' : '—'}</small></td>
            <td>${l.rating ? '<span class="rating-stars"><i class="bi bi-star-fill"></i></span> '+l.rating+'<br><small class="text-muted">('+( l.review_count || 0)+')</small>' : '—'}</td>
            <td><small>${escHtml(l.search_area || '—')}</small></td>
            <td><span class="badge-status badge-${l.lead_status}">${l.lead_status.replace(/_/g, ' ')}</span></td>
            <td><small class="text-muted">${l.created_at ? new Date(l.created_at).toLocaleDateString() : '—'}</small></td>
            <td>
                <div class="d-flex gap-1">
                    <a href="${APP_URL}/leads/${l.id}" class="btn btn-icon btn-sm btn-outline-primary" title="View"><i class="bi bi-eye"></i></a>
                    ${l.google_maps_url ? '<a href="'+escHtml(l.google_maps_url)+'" target="_blank" class="btn btn-icon btn-sm btn-outline-success" title="Maps"><i class="bi bi-geo-alt"></i></a>' : ''}
                    ${l.website_url && !l.email ? '<button class="btn btn-icon btn-sm btn-outline-warning" title="Find Email" onclick="findEmailForLead('+l.id+')"><i class="bi bi-envelope-at"></i></button>' : ''}
                </div>
            </td>
        </tr>
    `).join('');
}

function renderPagination(totalPages, current) {
    const ul = document.getElementById('leadsPagination');
    ul.innerHTML = '';

    if (totalPages <= 1) return;

    // Previous
    ul.innerHTML += `<li class="page-item ${current <= 1 ? 'disabled' : ''}"><a class="page-link" href="#" onclick="loadLeads(${current-1});return false;">‹</a></li>`;

    // Pages
    let start = Math.max(1, current - 2);
    let end = Math.min(totalPages, current + 2);

    for (let i = start; i <= end; i++) {
        ul.innerHTML += `<li class="page-item ${i === current ? 'active' : ''}"><a class="page-link" href="#" onclick="loadLeads(${i});return false;">${i}</a></li>`;
    }

    // Next
    ul.innerHTML += `<li class="page-item ${current >= totalPages ? 'disabled' : ''}"><a class="page-link" href="#" onclick="loadLeads(${current+1});return false;">›</a></li>`;
}

function clearFilters() {
    document.getElementById('filterSearch').value = '';
    document.getElementById('filterCategory').value = '';
    document.getElementById('filterStatus').value = '';
    document.getElementById('filterHasPhone').checked = false;
    document.getElementById('filterHasEmail').checked = false;
    document.getElementById('filterHasWebsite').checked = false;
    document.getElementById('filterMinRating').value = '';
    document.getElementById('filterMinReviews').value = '';
    document.getElementById('filterDateFrom').value = '';
    document.getElementById('filterDateTo').value = '';
    // Clear URL params
    window.history.replaceState({}, '', APP_URL + '/leads');
    loadLeads(1);
}

function findEmailForLead(id) {
    showToast('Searching for email...', 'info');
    fetch(APP_URL + '/api/leads/' + id + '/find-email', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Content-Type': 'application/json' },
        body: '{}'
    })
    .then(r => r.json())
    .then(res => {
        showToast(res.message, res.success ? 'success' : 'error');
        if (res.success) loadLeads(currentPage);
    });
}

function getSelectedIds() {
    return Array.from(document.querySelectorAll('#leadsBody .row-check:checked')).map(c => parseInt(c.value));
}

function executeBulk() {
    const action = document.getElementById('bulkAction').value;
    const ids = getSelectedIds();
    if (!ids.length) { showToast('Select at least one lead', 'error'); return; }

    if (action === 'status') {
        const status = document.getElementById('bulkStatusValue').value;
        apiPost('/api/leads/bulk-status', { ids, status }).then(res => {
            showToast(res.message, res.success ? 'success' : 'error');
            loadLeads(currentPage);
        });
    } else if (action === 'email') {
        showToast('Finding emails... this may take a while', 'info');
        apiPost('/api/leads/bulk-email', { ids }).then(res => {
            showToast(res.message, res.success ? 'success' : 'error');
            loadLeads(currentPage);
        });
    } else if (action === 'delete') {
        if (!confirm('Delete ' + ids.length + ' leads?')) return;
        apiPost('/api/leads/bulk-delete', { ids }).then(res => {
            showToast(res.message, res.success ? 'success' : 'error');
            loadLeads(currentPage);
        });
    } else if (action === 'export-csv' || action === 'export-xlsx') {
        exportSelected(action.replace('export-', ''));
    }
}

function exportFiltered(format) {
    const filters = getFilters();
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = APP_URL + '/api/export/' + format;
    form.innerHTML = `<input type="hidden" name="_csrf_token" value="${CSRF_TOKEN}">`;
    Object.entries(filters).forEach(([k, v]) => {
        form.innerHTML += `<input type="hidden" name="filters[${k}]" value="${escHtml(v)}">`;
    });
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function exportSelected(format) {
    const ids = getSelectedIds();
    if (!ids.length) { showToast('Select leads to export', 'error'); return; }
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = APP_URL + '/api/export/' + format;
    form.innerHTML = `<input type="hidden" name="_csrf_token" value="${CSRF_TOKEN}">`;
    ids.forEach(id => { form.innerHTML += `<input type="hidden" name="ids[]" value="${id}">`; });
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function switchView(view) {
    if (view === 'map') {
        document.getElementById('tableView').style.display = 'none';
        document.getElementById('mapViewContainer').style.display = 'block';
        document.getElementById('viewTable').classList.remove('active');
        document.getElementById('viewMap').classList.add('active');
        initMap();
    } else {
        document.getElementById('tableView').style.display = 'block';
        document.getElementById('mapViewContainer').style.display = 'none';
        document.getElementById('viewTable').classList.add('active');
        document.getElementById('viewMap').classList.remove('active');
    }
}

function initMap() {
    // Map view requires Google Maps JS API key
    if (typeof google === 'undefined') {
        document.getElementById('map-view').innerHTML = '<div class="empty-state py-5"><i class="bi bi-pin-map"></i><h5>Map View</h5><p>Configure Google Maps JavaScript API key in Settings to enable map view.</p></div>';
    }
}
</script>
