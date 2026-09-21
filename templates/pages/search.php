<!-- Lead Search Page -->
<div class="search-form-card">
    <div class="d-flex align-items-center gap-3 mb-4">
        <div style="width:48px;height:48px;background:linear-gradient(135deg,var(--primary),#7C3AED);border-radius:12px;display:flex;align-items:center;justify-content:center;color:white;font-size:22px;">
            <i class="bi bi-search"></i>
        </div>
        <div>
            <h5 class="fw-bold mb-0">Find Business Leads</h5>
            <small class="text-muted">Search for businesses using Google Places API</small>
        </div>
    </div>

    <form id="searchForm">
        <div class="row g-3">
            <!-- Category -->
            <div class="col-md-6">
                <label class="form-label">Business Category</label>
                <select class="form-select" id="categorySelect" name="category">
                    <option value="">Select category...</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= Validator::e($cat['name']) ?>"><?= Validator::e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Custom Category <small class="text-muted">(or type your own)</small></label>
                <input type="text" class="form-control" id="customCategory" name="custom_category"
                       placeholder="e.g. Chinese Restaurant, IT Company...">
            </div>

            <!-- Location -->
            <div class="col-md-4">
                <label class="form-label">Country</label>
                <select class="form-select" id="countrySelect" name="country">
                    <option value="">Select country...</option>
                    <?php foreach ($countries as $c): ?>
                        <option value="<?= Validator::e($c['name']) ?>" data-id="<?= $c['id'] ?>"
                            <?= ($c['name'] === ($defaultCountry ?? '')) ? 'selected' : '' ?>>
                            <?= Validator::e($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">City</label>
                <select class="form-select" id="citySelect" name="city" disabled>
                    <option value="">Select city...</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Area / Location</label>
                <select class="form-select" id="areaSelect" name="area" disabled>
                    <option value="">Select area...</option>
                </select>
            </div>

            <!-- Custom Location -->
            <div class="col-md-8">
                <label class="form-label">Custom Location <small class="text-muted">(override area dropdown)</small></label>
                <input type="text" class="form-control" id="customLocation" name="custom_location"
                       placeholder="e.g. Mirpur DOHS, Kazipara, Dhaka Cantonment...">
            </div>

            <!-- Max Results -->
            <div class="col-md-4">
                <label class="form-label">Maximum Leads</label>
                <select class="form-select" id="maxResults" name="max_results">
                    <option value="20">20</option>
                    <option value="40">40</option>
                    <option value="60" selected>60</option>
                    <option value="80">80</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>

        <div class="mt-4 d-flex align-items-center gap-3">
            <button type="submit" class="search-btn" id="searchBtn">
                <i class="bi bi-search"></i>
                <span>Search Leads</span>
            </button>
            <span class="text-muted" id="searchHint" style="font-size:13px;"></span>
        </div>

        <!-- Quick Filters -->
        <div class="quick-filters">
            <span class="text-muted me-2" style="font-size:12px; font-weight:600;">Quick:</span>
            <?php
            $quickFilters = ['Restaurant', 'Hotel', 'Hospital', 'Real Estate Agency', 'Travel Agency', 'Software Company', 'Digital Marketing Agency'];
            foreach ($quickFilters as $qf):
            ?>
                <button type="button" class="quick-filter-btn" onclick="setQuickFilter('<?= $qf ?>')"><?= $qf ?></button>
            <?php endforeach; ?>
        </div>
    </form>
</div>

<!-- Search Progress Modal -->
<div class="modal fade search-progress-modal" id="searchProgressModal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <div class="progress-spinner" id="progressSpinner"></div>
                <div class="progress-step" id="progressStep">Preparing search...</div>
                <div class="progress-detail" id="progressDetail">Please wait</div>

                <div class="progress-stats" id="progressStats" style="display:none;">
                    <div class="progress-stat">
                        <div class="value" id="psTotalFound">0</div>
                        <div class="label">Businesses Found</div>
                    </div>
                    <div class="progress-stat">
                        <div class="value" id="psNewLeads">0</div>
                        <div class="label">New Leads</div>
                    </div>
                    <div class="progress-stat">
                        <div class="value" id="psDuplicates">0</div>
                        <div class="label">Duplicates</div>
                    </div>
                    <div class="progress-stat">
                        <div class="value" id="psEmailsFound">0</div>
                        <div class="label">Emails Found</div>
                    </div>
                </div>

                <div id="progressComplete" style="display:none;" class="mt-4">
                    <div class="text-success mb-3"><i class="bi bi-check-circle-fill" style="font-size:48px;"></i></div>
                    <h5 class="fw-bold">Search Complete!</h5>
                    <div class="d-flex gap-2 justify-content-center mt-3">
                        <a href="#" class="btn btn-primary" id="viewResultsBtn">View Results</a>
                        <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>

                <div id="progressError" style="display:none;" class="mt-4">
                    <div class="text-danger mb-3"><i class="bi bi-exclamation-circle-fill" style="font-size:48px;"></i></div>
                    <h5 class="fw-bold">Search Failed</h5>
                    <p class="text-muted" id="errorMessage"></p>
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Results Section -->
<div id="searchResults" style="display:none;">
    <div class="content-card">
        <div class="card-header">
            <h6><i class="bi bi-list-check me-2"></i>Search Results</h6>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-primary" onclick="exportResults('csv')">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i>CSV
                </button>
                <button class="btn btn-sm btn-outline-success" onclick="exportResults('xlsx')">
                    <i class="bi bi-file-earmark-excel me-1"></i>XLSX
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table" id="resultsTable">
                    <thead>
                        <tr>
                            <th><input type="checkbox" class="form-check-input" id="selectAllResults"></th>
                            <th>Business Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Website</th>
                            <th>Rating</th>
                            <th>Area</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="resultsBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load cities for default country
    const countrySelect = document.getElementById('countrySelect');
    if (countrySelect.value) {
        const selectedOption = countrySelect.selectedOptions[0];
        const countryId = selectedOption.dataset.id;
        if (countryId) loadCities(countryId);
    }

    // Country → City cascade
    countrySelect.addEventListener('change', function() {
        const selectedOption = this.selectedOptions[0];
        const countryId = selectedOption.dataset.id;
        document.getElementById('citySelect').innerHTML = '<option value="">Select city...</option>';
        document.getElementById('areaSelect').innerHTML = '<option value="">Select area...</option>';
        document.getElementById('citySelect').disabled = true;
        document.getElementById('areaSelect').disabled = true;
        if (countryId) loadCities(countryId);
    });

    // City → Area cascade
    document.getElementById('citySelect').addEventListener('change', function() {
        const selectedOption = this.selectedOptions[0];
        const cityId = selectedOption.dataset.id;
        document.getElementById('areaSelect').innerHTML = '<option value="">Select area...</option>';
        document.getElementById('areaSelect').disabled = true;
        if (cityId) loadAreas(cityId);
    });

    // Search form submit
    document.getElementById('searchForm').addEventListener('submit', function(e) {
        e.preventDefault();
        startSearch();
    });

    // Select all checkbox
    document.getElementById('selectAllResults').addEventListener('change', function() {
        document.querySelectorAll('#resultsBody .row-check').forEach(cb => cb.checked = this.checked);
    });
});

function loadCities(countryId) {
    fetch(APP_URL + '/api/cities/' + countryId, {
        headers: { 'X-CSRF-TOKEN': CSRF_TOKEN }
    })
    .then(r => r.json())
    .then(res => {
        const sel = document.getElementById('citySelect');
        sel.innerHTML = '<option value="">Select city...</option>';
        if (res.success && res.data) {
            res.data.forEach(c => {
                sel.innerHTML += `<option value="${escHtml(c.name)}" data-id="${c.id}">${escHtml(c.name)}</option>`;
            });
        }
        sel.disabled = false;
    });
}

function loadAreas(cityId) {
    fetch(APP_URL + '/api/areas/' + cityId, {
        headers: { 'X-CSRF-TOKEN': CSRF_TOKEN }
    })
    .then(r => r.json())
    .then(res => {
        const sel = document.getElementById('areaSelect');
        sel.innerHTML = '<option value="">Select area...</option>';
        if (res.success && res.data) {
            res.data.forEach(a => {
                sel.innerHTML += `<option value="${escHtml(a.name)}" data-id="${a.id}">${escHtml(a.name)}</option>`;
            });
        }
        sel.disabled = false;
    });
}

function setQuickFilter(category) {
    document.getElementById('categorySelect').value = category;
    document.getElementById('customCategory').value = '';
    document.querySelectorAll('.quick-filter-btn').forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');
}

function startSearch() {
    const btn = document.getElementById('searchBtn');
    const category = document.getElementById('customCategory').value || document.getElementById('categorySelect').value;

    if (!category) {
        showToast('Please select or enter a business category', 'error');
        return;
    }

    btn.disabled = true;

    // Show progress modal
    const modal = new bootstrap.Modal(document.getElementById('searchProgressModal'));
    document.getElementById('progressSpinner').style.display = 'block';
    document.getElementById('progressStep').textContent = 'Searching Google Places...';
    document.getElementById('progressDetail').textContent = 'Finding businesses matching your criteria';
    document.getElementById('progressStats').style.display = 'none';
    document.getElementById('progressComplete').style.display = 'none';
    document.getElementById('progressError').style.display = 'none';
    modal.show();

    const formData = new FormData(document.getElementById('searchForm'));
    formData.append('_csrf_token', CSRF_TOKEN);

    fetch(APP_URL + '/api/search/start', {
        method: 'POST',
        body: formData,
        headers: { 'X-CSRF-TOKEN': CSRF_TOKEN }
    })
    .then(r => r.json())
    .then(res => {
        btn.disabled = false;

        if (res.success) {
            const stats = res.data.stats || {};

            document.getElementById('progressSpinner').style.display = 'none';
            document.getElementById('progressStats').style.display = 'grid';
            document.getElementById('psTotalFound').textContent = stats.total_found || 0;
            document.getElementById('psNewLeads').textContent = stats.new_leads || 0;
            document.getElementById('psDuplicates').textContent = stats.duplicates || 0;
            document.getElementById('psEmailsFound').textContent = stats.emails_found || 0;

            document.getElementById('progressStep').textContent = 'Search Complete!';
            document.getElementById('progressDetail').textContent = res.message;
            document.getElementById('progressComplete').style.display = 'block';

            const jobId = res.data.job_id;
            document.getElementById('viewResultsBtn').href = APP_URL + '/leads?search_job_id=' + jobId;

            // Load results into table
            loadSearchResults(jobId);
        } else {
            document.getElementById('progressSpinner').style.display = 'none';
            document.getElementById('progressError').style.display = 'block';
            document.getElementById('errorMessage').textContent = res.message || 'Search failed';
        }
    })
    .catch(err => {
        btn.disabled = false;
        document.getElementById('progressSpinner').style.display = 'none';
        document.getElementById('progressError').style.display = 'block';
        document.getElementById('errorMessage').textContent = 'Network error. Please try again.';
    });
}

function loadSearchResults(jobId) {
    fetch(APP_URL + '/api/search/results/' + jobId, {
        headers: { 'X-CSRF-TOKEN': CSRF_TOKEN }
    })
    .then(r => r.json())
    .then(res => {
        if (res.success && res.data && res.data.data) {
            renderResultsTable(res.data.data);
            document.getElementById('searchResults').style.display = 'block';
        }
    });
}

function renderResultsTable(leads) {
    const tbody = document.getElementById('resultsBody');
    tbody.innerHTML = '';

    leads.forEach(lead => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><input type="checkbox" class="form-check-input row-check" value="${lead.id}"></td>
            <td>
                <a href="${APP_URL}/leads/${lead.id}" class="text-decoration-none fw-semibold">${escHtml(lead.business_name)}</a>
                <br><small class="text-muted">${escHtml(lead.primary_category || '')}</small>
            </td>
            <td><small>${escHtml(lead.national_phone || '—')}</small></td>
            <td><small>${escHtml(lead.email || '—')}</small></td>
            <td>${lead.website_url ? '<a href="'+escHtml(lead.website_url)+'" target="_blank" class="text-decoration-none"><i class="bi bi-globe"></i></a>' : '—'}</td>
            <td>${lead.rating ? '<span class="rating-stars"><i class="bi bi-star-fill"></i></span> ' + lead.rating : '—'}</td>
            <td><small>${escHtml(lead.search_area || '')}</small></td>
            <td><span class="badge-status badge-${lead.lead_status}">${lead.lead_status.replace('_', ' ')}</span></td>
            <td>
                <div class="d-flex gap-1">
                    <a href="${APP_URL}/leads/${lead.id}" class="btn btn-icon btn-sm btn-outline-primary" title="View"><i class="bi bi-eye"></i></a>
                    ${lead.google_maps_url ? '<a href="'+escHtml(lead.google_maps_url)+'" target="_blank" class="btn btn-icon btn-sm btn-outline-success" title="Google Maps"><i class="bi bi-geo-alt"></i></a>' : ''}
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function exportResults(format) {
    const checked = Array.from(document.querySelectorAll('#resultsBody .row-check:checked')).map(c => parseInt(c.value));

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = APP_URL + '/api/export/' + format;
    form.innerHTML = `<input type="hidden" name="_csrf_token" value="${CSRF_TOKEN}">`;

    if (checked.length > 0) {
        checked.forEach(id => {
            form.innerHTML += `<input type="hidden" name="ids[]" value="${id}">`;
        });
    }

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
</script>
