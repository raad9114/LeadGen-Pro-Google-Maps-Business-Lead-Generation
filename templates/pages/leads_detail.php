<!-- Lead Detail Page -->
<?php if (isset($lead)): ?>
<div class="mb-4">
    <a href="<?= Config::appUrl() ?>/leads" class="text-decoration-none"><i class="bi bi-arrow-left me-1"></i>Back to Leads</a>
</div>

<div class="row g-4">
    <!-- Lead Info -->
    <div class="col-xl-8">
        <div class="content-card">
            <div class="card-body">
                <div class="lead-detail-header">
                    <div class="lead-icon"><i class="bi bi-building"></i></div>
                    <div>
                        <h3><?= Validator::e($lead['business_name']) ?></h3>
                        <span class="badge-status badge-<?= $lead['lead_status'] ?>"><?= ucfirst(str_replace('_', ' ', $lead['lead_status'])) ?></span>
                        <?php if ($lead['rating']): ?>
                            <span class="ms-2 rating-stars"><i class="bi bi-star-fill"></i> <?= $lead['rating'] ?></span>
                            <small class="text-muted">(<?= $lead['review_count'] ?? 0 ?> reviews)</small>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="info-row"><div class="info-label">Category</div><div class="info-value"><?= Validator::e($lead['primary_category'] ?? $lead['search_category'] ?? '—') ?></div></div>
                <div class="info-row"><div class="info-label">Phone</div><div class="info-value"><?= Validator::e($lead['national_phone'] ?? '—') ?></div></div>
                <div class="info-row"><div class="info-label">International Phone</div><div class="info-value"><?= Validator::e($lead['international_phone'] ?? '—') ?></div></div>
                <div class="info-row"><div class="info-label">Email</div><div class="info-value"><?= $lead['email'] ? '<a href="mailto:' . Validator::e($lead['email']) . '">' . Validator::e($lead['email']) . '</a>' : '—' ?></div></div>
                <div class="info-row"><div class="info-label">Address</div><div class="info-value"><?= Validator::e($lead['formatted_address'] ?? '—') ?></div></div>
                <div class="info-row"><div class="info-label">Website</div><div class="info-value"><?= $lead['website_url'] ? '<a href="' . Validator::e($lead['website_url']) . '" target="_blank">' . Validator::e($lead['website_url']) . '</a>' : '—' ?></div></div>
                <div class="info-row"><div class="info-label">Google Maps</div><div class="info-value"><?= $lead['google_maps_url'] ? '<a href="' . Validator::e($lead['google_maps_url']) . '" target="_blank"><i class="bi bi-geo-alt me-1"></i>View on Map</a>' : '—' ?></div></div>
                <div class="info-row"><div class="info-label">Location</div><div class="info-value"><?= Validator::e(implode(', ', array_filter([$lead['search_area'], $lead['search_city'], $lead['search_country']])) ?: '—') ?></div></div>
                <div class="info-row"><div class="info-label">Business Status</div><div class="info-value"><?= Validator::e($lead['business_status'] ?? '—') ?></div></div>
                <div class="info-row"><div class="info-label">Place ID</div><div class="info-value"><code style="font-size:11px;"><?= Validator::e($lead['place_id']) ?></code></div></div>
                <div class="info-row"><div class="info-label">Date Collected</div><div class="info-value"><?= $lead['created_at'] ?></div></div>
                <div class="info-row"><div class="info-label">Last API Refresh</div><div class="info-value"><?= $lead['last_api_refresh'] ?? '—' ?></div></div>
            </div>
        </div>

        <!-- Emails Found -->
        <?php if (!empty($emails)): ?>
        <div class="content-card">
            <div class="card-header"><h6><i class="bi bi-envelope me-2"></i>Email Addresses Found</h6></div>
            <div class="card-body p-0">
                <table class="table">
                    <thead><tr><th>Email</th><th>Source</th><th>Primary</th></tr></thead>
                    <tbody>
                        <?php foreach ($emails as $em): ?>
                        <tr>
                            <td><a href="mailto:<?= Validator::e($em['email']) ?>"><?= Validator::e($em['email']) ?></a></td>
                            <td><small class="text-muted"><?= Validator::e($em['source_url'] ?? '—') ?></small></td>
                            <td><?= $em['is_primary'] ? '<span class="badge bg-primary">Primary</span>' : '' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar: Status + Notes + Actions -->
    <div class="col-xl-4">
        <!-- Actions -->
        <div class="content-card mb-4">
            <div class="card-header"><h6><i class="bi bi-lightning me-2"></i>Actions</h6></div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <?php if ($lead['website_url']): ?>
                        <button class="btn btn-warning btn-sm" onclick="findEmailDetail(<?= $lead['id'] ?>)"><i class="bi bi-envelope-at me-1"></i>Find Email</button>
                        <a href="<?= Validator::e($lead['website_url']) ?>" target="_blank" class="btn btn-info btn-sm text-white"><i class="bi bi-globe me-1"></i>Open Website</a>
                    <?php endif; ?>
                    <?php if ($lead['google_maps_url']): ?>
                        <a href="<?= Validator::e($lead['google_maps_url']) ?>" target="_blank" class="btn btn-success btn-sm"><i class="bi bi-geo-alt me-1"></i>Open Google Maps</a>
                    <?php endif; ?>
                    <button class="btn btn-outline-primary btn-sm" onclick="refreshLead(<?= $lead['id'] ?>)"><i class="bi bi-arrow-clockwise me-1"></i>Refresh Details</button>
                    <button class="btn btn-outline-danger btn-sm" onclick="deleteLead(<?= $lead['id'] ?>)"><i class="bi bi-trash me-1"></i>Delete Lead</button>
                </div>
            </div>
        </div>

        <!-- Status -->
        <div class="content-card mb-4">
            <div class="card-header"><h6><i class="bi bi-flag me-2"></i>Lead Status</h6></div>
            <div class="card-body">
                <select class="form-select" id="leadStatusSelect" onchange="updateLeadStatus(<?= $lead['id'] ?>, this.value)">
                    <?php
                    $statuses = ['new','not_contacted','contacted','follow_up','interested','converted','not_interested','invalid'];
                    foreach ($statuses as $s): ?>
                        <option value="<?= $s ?>" <?= $lead['lead_status'] === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $s)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Notes -->
        <div class="content-card mb-4">
            <div class="card-header"><h6><i class="bi bi-chat-dots me-2"></i>Notes</h6></div>
            <div class="card-body">
                <div class="mb-3">
                    <textarea class="form-control" id="newNote" rows="3" placeholder="Add a note..."></textarea>
                    <button class="btn btn-sm btn-primary mt-2" onclick="addNote(<?= $lead['id'] ?>)">Add Note</button>
                </div>
                <div id="notesContainer">
                    <?php foreach ($notes as $note): ?>
                        <div class="note-item">
                            <div><?= nl2br(Validator::e($note['note'])) ?></div>
                            <div class="note-meta">
                                <?= Validator::e($note['full_name'] ?? $note['username']) ?> · <?= date('M d, Y H:i', strtotime($note['created_at'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($notes)): ?>
                        <p class="text-muted small mb-0">No notes yet</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Activity Timeline -->
        <div class="content-card">
            <div class="card-header"><h6><i class="bi bi-clock-history me-2"></i>Activity</h6></div>
            <div class="card-body">
                <?php foreach ($activities as $act): ?>
                    <div class="activity-item">
                        <div class="activity-dot"></div>
                        <div class="activity-content">
                            <div class="activity-action"><?= Validator::e($act['action']) ?>: <?= Validator::e($act['details'] ?? '') ?></div>
                            <div class="activity-time"><?= Validator::e($act['username'] ?? 'System') ?> · <?= date('M d, H:i', strtotime($act['created_at'])) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($activities)): ?>
                    <p class="text-muted small mb-0">No activity yet</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function updateLeadStatus(id, status) {
    apiPut('/api/leads/' + id, { lead_status: status }).then(res => {
        showToast(res.message, res.success ? 'success' : 'error');
    });
}

function addNote(leadId) {
    const note = document.getElementById('newNote').value.trim();
    if (!note) return;

    apiPost('/api/leads/' + leadId + '/notes', { note }).then(res => {
        if (res.success) {
            document.getElementById('newNote').value = '';
            location.reload();
        }
        showToast(res.message, res.success ? 'success' : 'error');
    });
}

function findEmailDetail(id) {
    showToast('Searching for email addresses...', 'info');
    apiPost('/api/leads/' + id + '/find-email', {}).then(res => {
        showToast(res.message, res.success ? 'success' : 'error');
        if (res.success) location.reload();
    });
}

function refreshLead(id) {
    showToast('Refreshing business details...', 'info');
    apiPost('/api/leads/' + id + '/refresh', {}).then(res => {
        showToast(res.message, res.success ? 'success' : 'error');
        if (res.success) location.reload();
    });
}

function deleteLead(id) {
    if (!confirm('Are you sure you want to delete this lead?')) return;
    apiDelete('/api/leads/' + id).then(res => {
        showToast(res.message, res.success ? 'success' : 'error');
        if (res.success) window.location.href = APP_URL + '/leads';
    });
}
</script>
<?php endif; ?>
