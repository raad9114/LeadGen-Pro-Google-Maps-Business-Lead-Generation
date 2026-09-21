<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install — LeadGen Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= Config::appUrl() ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>

<div class="install-page">
    <div class="install-card">
        <div class="text-center mb-4">
            <div class="brand-icon d-inline-flex mb-3" style="width:56px;height:56px;background:linear-gradient(135deg,#4F46E5,#818CF8);border-radius:14px;align-items:center;justify-content:center;font-size:28px;color:white;">
                <i class="bi bi-geo-alt-fill"></i>
            </div>
            <h3 class="fw-bold">Install LeadGen Pro</h3>
            <p class="text-muted">Set up your lead generation platform</p>
        </div>

        <!-- System Checks -->
        <div class="mb-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-shield-check me-2"></i>System Requirements</h6>
            <?php foreach ($checks as $check): ?>
                <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                    <span class="fw-medium"><?= $check['label'] ?></span>
                    <span class="badge bg-<?= $check['pass'] ? 'success' : 'danger' ?>">
                        <?= $check['value'] ?> <?= $check['pass'] ? '✓' : '✗' ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>

        <?php foreach (Session::getFlash('error') as $msg): ?>
            <div class="alert alert-danger py-2 px-3" style="font-size:13px;">
                <i class="bi bi-exclamation-circle me-1"></i> <?= Validator::e($msg) ?>
            </div>
        <?php endforeach; ?>

        <form method="POST" action="<?= Config::appUrl() ?>/install">
            <?= CSRF::field() ?>

            <h6 class="fw-bold mt-4 mb-3"><i class="bi bi-database me-2"></i>Database Configuration</h6>

            <div class="row g-3">
                <div class="col-8">
                    <label class="form-label fw-semibold small">Database Host</label>
                    <input type="text" class="form-control" name="db_host" value="localhost" required>
                </div>
                <div class="col-4">
                    <label class="form-label fw-semibold small">Port</label>
                    <input type="text" class="form-control" name="db_port" value="3306" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold small">Database Name</label>
                    <input type="text" class="form-control" name="db_name" value="lead_generation" required>
                </div>
                <div class="col-6">
                    <label class="form-label fw-semibold small">DB Username</label>
                    <input type="text" class="form-control" name="db_user" value="root" required>
                </div>
                <div class="col-6">
                    <label class="form-label fw-semibold small">DB Password</label>
                    <input type="password" class="form-control" name="db_pass" value="">
                </div>
            </div>

            <h6 class="fw-bold mt-4 mb-3"><i class="bi bi-person-circle me-2"></i>Admin Account</h6>

            <div class="row g-3">
                <div class="col-6">
                    <label class="form-label fw-semibold small">Admin Username</label>
                    <input type="text" class="form-control" name="admin_username" value="admin" required>
                </div>
                <div class="col-6">
                    <label class="form-label fw-semibold small">Admin Password</label>
                    <input type="password" class="form-control" name="admin_password" value="admin123" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold small">Admin Email</label>
                    <input type="email" class="form-control" name="admin_email" value="admin@leadgen.local" required>
                </div>
            </div>

            <h6 class="fw-bold mt-4 mb-3"><i class="bi bi-key me-2"></i>Google API (Optional — can set later)</h6>

            <div class="mb-3">
                <label class="form-label fw-semibold small">Google Places API Key</label>
                <input type="text" class="form-control" name="api_key" placeholder="AIza...">
                <div class="form-text">Server-restricted key with Places API (New) enabled</div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 mt-3" style="font-size:15px; font-weight:700;">
                <i class="bi bi-rocket-takeoff me-2"></i>Install Application
            </button>
        </form>
    </div>
</div>

</body>
</html>
