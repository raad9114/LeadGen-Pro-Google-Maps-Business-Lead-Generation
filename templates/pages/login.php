<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — <?= Validator::e(Config::appName()) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= Config::appUrl() ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>

<div class="login-page">
    <div class="login-card">
        <div class="brand">
            <div class="brand-icon"><i class="bi bi-geo-alt-fill"></i></div>
            <h3><?= Validator::e(Config::appName()) ?></h3>
            <p>Business Lead Generation Platform</p>
        </div>

        <?php foreach (Session::getFlash('error') as $msg): ?>
            <div class="alert alert-danger py-2 px-3" style="font-size:13px; border-radius:8px;">
                <i class="bi bi-exclamation-circle me-1"></i> <?= Validator::e($msg) ?>
            </div>
        <?php endforeach; ?>

        <?php foreach (Session::getFlash('success') as $msg): ?>
            <div class="alert alert-success py-2 px-3" style="font-size:13px; border-radius:8px;">
                <i class="bi bi-check-circle me-1"></i> <?= Validator::e($msg) ?>
            </div>
        <?php endforeach; ?>

        <form method="POST" action="<?= Config::appUrl() ?>/login">
            <?= CSRF::field() ?>

            <div class="mb-3">
                <label class="form-label fw-semibold" for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Enter username"
                       required autofocus style="border-radius:8px; padding:10px 14px;">
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold" for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter password"
                       required style="border-radius:8px; padding:10px 14px;">
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2" style="font-size:15px; font-weight:700;">
                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
            </button>
        </form>

        <div class="text-center mt-4">
            <small class="text-muted">Powered by Google Places API</small>
        </div>
    </div>
</div>

</body>
</html>
