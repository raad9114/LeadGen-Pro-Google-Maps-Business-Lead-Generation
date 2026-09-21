<!-- Settings Page -->
<form id="settingsForm">
    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Google API -->
            <div class="content-card mb-4">
                <div class="card-header"><h6><i class="bi bi-key me-2"></i>Google API Configuration</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Google Places API Key <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="google_places_api_key"
                               value="<?= Validator::e($settings['google_places_api_key'] ?? '') ?>"
                               placeholder="AIza...">
                        <div class="form-text">Server-restricted key with Places API (New) enabled</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Google Maps JavaScript API Key</label>
                        <input type="password" class="form-control" name="google_maps_js_api_key"
                               value="<?= Validator::e($settings['google_maps_js_api_key'] ?? '') ?>"
                               placeholder="AIza...">
                        <div class="form-text">Browser-restricted key for Map View (optional)</div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Requests Per Minute</label>
                            <input type="number" class="form-control" name="requests_per_minute"
                                   value="<?= Validator::e($settings['requests_per_minute'] ?? '30') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Cost Per Text Search ($)</label>
                            <input type="text" class="form-control" name="cost_per_text_search"
                                   value="<?= Validator::e($settings['cost_per_text_search'] ?? '0.032') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search Settings -->
            <div class="content-card mb-4">
                <div class="card-header"><h6><i class="bi bi-search me-2"></i>Search Settings</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Max Results Per Search</label>
                            <input type="number" class="form-control" name="max_results_per_search"
                                   value="<?= Validator::e($settings['max_results_per_search'] ?? '60') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Default Country</label>
                            <input type="text" class="form-control" name="default_country"
                                   value="<?= Validator::e($settings['default_country'] ?? 'Bangladesh') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email Enrichment -->
            <div class="content-card mb-4">
                <div class="card-header"><h6><i class="bi bi-envelope me-2"></i>Email Enrichment Settings</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Max Pages to Crawl</label>
                            <input type="number" class="form-control" name="max_pages_to_crawl"
                                   value="<?= Validator::e($settings['max_pages_to_crawl'] ?? '5') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Crawl Timeout (seconds)</label>
                            <input type="number" class="form-control" name="crawl_timeout_seconds"
                                   value="<?= Validator::e($settings['crawl_timeout_seconds'] ?? '5') ?>">
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-3">
                                <input class="form-check-input" type="checkbox" name="enable_email_enrichment" value="1"
                                       id="enableEmail" <?= ($settings['enable_email_enrichment'] ?? '1') === '1' ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="enableEmail">Enable Email Enrichment</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-3">
                                <input class="form-check-input" type="checkbox" name="auto_search_emails" value="1"
                                       id="autoEmail" <?= ($settings['auto_search_emails'] ?? '1') === '1' ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="autoEmail">Auto-Search Emails After Search</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i>Save Settings</button>
        </div>

        <div class="col-lg-4">
            <div class="content-card">
                <div class="card-header"><h6><i class="bi bi-info-circle me-2"></i>Help</h6></div>
                <div class="card-body">
                    <h6 class="fw-bold mb-2" style="font-size:14px;">Getting a Google API Key</h6>
                    <ol class="small text-muted" style="padding-left:16px;">
                        <li>Go to <a href="https://console.cloud.google.com" target="_blank">Google Cloud Console</a></li>
                        <li>Create or select a project</li>
                        <li>Enable <strong>Places API (New)</strong></li>
                        <li>Create an API key under <em>Credentials</em></li>
                        <li>Restrict the key to your server's IP</li>
                        <li>Ensure billing is enabled</li>
                    </ol>

                    <hr>

                    <h6 class="fw-bold mb-2" style="font-size:14px;">Important Notes</h6>
                    <ul class="small text-muted" style="padding-left:16px;">
                        <li>API key is stored securely and never exposed to the browser</li>
                        <li>Rate limiting protects against quota exhaustion</li>
                        <li>Email enrichment only crawls public business websites</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
document.getElementById('settingsForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const data = {};
    formData.forEach((v, k) => { data[k] = v; });

    // Handle checkboxes (unchecked ones don't appear in FormData)
    if (!data.enable_email_enrichment) data.enable_email_enrichment = '0';
    if (!data.auto_search_emails) data.auto_search_emails = '0';

    apiPost('/api/settings', data).then(res => {
        showToast(res.message, res.success ? 'success' : 'error');
    });
});
</script>
