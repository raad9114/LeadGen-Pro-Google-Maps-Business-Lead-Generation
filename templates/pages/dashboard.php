<!-- Dashboard Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon primary"><i class="bi bi-people-fill"></i></div>
            <div class="stat-value" id="statTotal"><?= number_format($leadStats['total'] ?? 0) ?></div>
            <div class="stat-label">Total Leads</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon info"><i class="bi bi-star-fill"></i></div>
            <div class="stat-value" id="statNew"><?= number_format($leadStats['new'] ?? 0) ?></div>
            <div class="stat-label">New Leads</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon success"><i class="bi bi-telephone-fill"></i></div>
            <div class="stat-value" id="statPhone"><?= number_format($leadStats['with_phone'] ?? 0) ?></div>
            <div class="stat-label">With Phone</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon warning"><i class="bi bi-envelope-fill"></i></div>
            <div class="stat-value" id="statEmail"><?= number_format($leadStats['with_email'] ?? 0) ?></div>
            <div class="stat-label">With Email</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon info"><i class="bi bi-globe"></i></div>
            <div class="stat-value"><?= number_format($leadStats['with_website'] ?? 0) ?></div>
            <div class="stat-label">With Website</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon success"><i class="bi bi-check-circle-fill"></i></div>
            <div class="stat-value"><?= number_format($leadStats['converted'] ?? 0) ?></div>
            <div class="stat-label">Converted</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon primary"><i class="bi bi-search"></i></div>
            <div class="stat-value"><?= number_format($searchJobModel->getTodayCount()) ?></div>
            <div class="stat-label">Searches Today</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon danger"><i class="bi bi-lightning-fill"></i></div>
            <div class="stat-value"><?= number_format($apiStats['today'] ?? 0) ?></div>
            <div class="stat-label">API Requests Today</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Searches -->
    <div class="col-xl-6">
        <div class="content-card">
            <div class="card-header">
                <h6><i class="bi bi-clock-history me-2"></i>Recent Searches</h6>
                <a href="<?= Config::appUrl() ?>/history" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentSearches)): ?>
                    <div class="empty-state py-4">
                        <p class="mb-0">No searches yet. <a href="<?= Config::appUrl() ?>/search">Start searching!</a></p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Search</th>
                                    <th>Results</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentSearches as $search): ?>
                                <tr>
                                    <td>
                                        <strong><?= Validator::e($search['category_name']) ?></strong>
                                        <br><small class="text-muted"><?= Validator::e($search['area_name'] ? $search['area_name'] . ', ' . $search['city_name'] : $search['city_name'] ?? '') ?></small>
                                    </td>
                                    <td><span class="fw-semibold"><?= $search['total_found'] ?></span></td>
                                    <td><span class="badge-status badge-job-<?= $search['status'] ?>"><?= ucfirst(str_replace('_', ' ', $search['status'])) ?></span></td>
                                    <td><small class="text-muted"><?= date('M d, H:i', strtotime($search['created_at'])) ?></small></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Leads -->
    <div class="col-xl-6">
        <div class="content-card">
            <div class="card-header">
                <h6><i class="bi bi-people me-2"></i>Recent Leads</h6>
                <a href="<?= Config::appUrl() ?>/leads" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentLeads)): ?>
                    <div class="empty-state py-4">
                        <p class="mb-0">No leads yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Business</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentLeads as $lead): ?>
                                <tr>
                                    <td>
                                        <a href="<?= Config::appUrl() ?>/leads/<?= $lead['id'] ?>" class="text-decoration-none fw-semibold"><?= Validator::e($lead['business_name']) ?></a>
                                        <br><small class="text-muted"><?= Validator::e($lead['search_category'] ?? '') ?> · <?= Validator::e($lead['search_area'] ?? '') ?></small>
                                    </td>
                                    <td><small><?= Validator::e($lead['national_phone'] ?? '—') ?></small></td>
                                    <td><span class="badge-status badge-<?= $lead['lead_status'] ?>"><?= ucfirst(str_replace('_', ' ', $lead['lead_status'])) ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Category & Area Distribution -->
<div class="row g-4 mt-0">
    <div class="col-xl-6">
        <div class="content-card">
            <div class="card-header"><h6><i class="bi bi-bar-chart me-2"></i>Leads by Category</h6></div>
            <div class="card-body">
                <?php if (empty($byCategory)): ?>
                    <p class="text-muted mb-0">No data yet</p>
                <?php else: ?>
                    <?php $maxCat = max(array_column($byCategory, 'count')); ?>
                    <?php foreach ($byCategory as $cat): ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="fw-semibold"><?= Validator::e($cat['search_category']) ?></small>
                                <small class="text-muted"><?= $cat['count'] ?></small>
                            </div>
                            <div class="progress" style="height:6px; border-radius:3px;">
                                <div class="progress-bar" style="width:<?= ($cat['count'] / $maxCat * 100) ?>%; background:var(--primary); border-radius:3px;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="content-card">
            <div class="card-header"><h6><i class="bi bi-pin-map me-2"></i>Leads by Area</h6></div>
            <div class="card-body">
                <?php if (empty($byArea)): ?>
                    <p class="text-muted mb-0">No data yet</p>
                <?php else: ?>
                    <?php $maxArea = max(array_column($byArea, 'count')); ?>
                    <?php foreach ($byArea as $area): ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="fw-semibold"><?= Validator::e($area['search_area']) ?></small>
                                <small class="text-muted"><?= $area['count'] ?></small>
                            </div>
                            <div class="progress" style="height:6px; border-radius:3px;">
                                <div class="progress-bar bg-success" style="width:<?= ($area['count'] / $maxArea * 100) ?>%; border-radius:3px;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
