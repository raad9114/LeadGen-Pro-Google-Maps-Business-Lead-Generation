<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="LeadGen Pro — Business Lead Generation Dashboard">
    <title><?= Validator::e($pageTitle ?? 'Dashboard') ?> — <?= Validator::e(Config::appName()) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= Config::appUrl() ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>

<!-- Sidebar Overlay (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon"><i class="bi bi-geo-alt-fill"></i></div>
        <div>
            <h5><?= Validator::e(Config::appName()) ?></h5>
            <small>Lead Generation</small>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">Main</div>
        <a href="<?= Config::appUrl() ?>/dashboard" class="nav-link <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>">
            <i class="bi bi-grid-1x2-fill"></i> Dashboard
        </a>
        <a href="<?= Config::appUrl() ?>/search" class="nav-link <?= ($currentPage ?? '') === 'search' ? 'active' : '' ?>">
            <i class="bi bi-search"></i> Lead Search
        </a>
        <a href="<?= Config::appUrl() ?>/leads" class="nav-link <?= ($currentPage ?? '') === 'leads' ? 'active' : '' ?>">
            <i class="bi bi-people-fill"></i> All Leads
        </a>
        <a href="<?= Config::appUrl() ?>/history" class="nav-link <?= ($currentPage ?? '') === 'history' ? 'active' : '' ?>">
            <i class="bi bi-clock-history"></i> Search History
        </a>

        <div class="nav-section">Manage</div>
        <a href="<?= Config::appUrl() ?>/categories" class="nav-link <?= ($currentPage ?? '') === 'categories' ? 'active' : '' ?>">
            <i class="bi bi-tags-fill"></i> Categories
        </a>
        <a href="<?= Config::appUrl() ?>/locations" class="nav-link <?= ($currentPage ?? '') === 'locations' ? 'active' : '' ?>">
            <i class="bi bi-pin-map-fill"></i> Locations
        </a>
        <a href="<?= Config::appUrl() ?>/exports" class="nav-link <?= ($currentPage ?? '') === 'exports' ? 'active' : '' ?>">
            <i class="bi bi-download"></i> Exports
        </a>

        <?php if (Session::isAdmin()): ?>
        <div class="nav-section">Admin</div>
        <a href="<?= Config::appUrl() ?>/api-usage" class="nav-link <?= ($currentPage ?? '') === 'api-usage' ? 'active' : '' ?>">
            <i class="bi bi-activity"></i> API Usage
        </a>
        <a href="<?= Config::appUrl() ?>/users" class="nav-link <?= ($currentPage ?? '') === 'users' ? 'active' : '' ?>">
            <i class="bi bi-person-gear"></i> Users
        </a>
        <a href="<?= Config::appUrl() ?>/settings" class="nav-link <?= ($currentPage ?? '') === 'settings' ? 'active' : '' ?>">
            <i class="bi bi-gear-fill"></i> Settings
        </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="<?= Config::appUrl() ?>/logout" class="nav-link" style="color:var(--danger);">
            <i class="bi bi-box-arrow-left"></i> Logout
        </a>
    </div>
</aside>

<!-- Main Content -->
<main class="main-content">
    <!-- Top Header -->
    <header class="top-header">
        <div class="d-flex align-items-center gap-3">
            <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
            <h1 class="page-title"><?= Validator::e($pageTitle ?? 'Dashboard') ?></h1>
        </div>
        <div class="header-actions">
            <div class="user-info">
                <span class="d-none d-md-inline"><?= Validator::e(Session::get('user_name', 'User')) ?></span>
                <div class="user-avatar"><?= strtoupper(substr(Session::get('user_name', 'U'), 0, 1)) ?></div>
            </div>
        </div>
    </header>

    <!-- Page Content -->
    <div class="page-content">
        <?php
        // Include the correct page template
        $page = $currentPage ?? 'dashboard';
        $pageFile = TEMPLATE_PATH . '/pages/' . $page . '.php';
        if (file_exists($pageFile)) {
            include $pageFile;
        } else {
            echo '<div class="empty-state"><i class="bi bi-exclamation-circle"></i><h5>Page not found</h5></div>';
        }
        ?>
    </div>
</main>

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<!-- CSRF Token for JS -->
<script>
    const APP_URL = '<?= Config::appUrl() ?>';
    const CSRF_TOKEN = '<?= CSRF::token() ?>';
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= Config::appUrl() ?>/assets/js/app.js"></script>

<?php
// Page-specific scripts
$jsFile = TEMPLATE_PATH . '/pages/' . $page . '.js.php';
if (file_exists($jsFile)) {
    include $jsFile;
}
?>

</body>
</html>
