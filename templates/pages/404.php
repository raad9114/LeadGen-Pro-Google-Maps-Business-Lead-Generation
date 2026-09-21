<div class="empty-state" style="margin-top:100px;">
    <i class="bi bi-exclamation-circle" style="font-size:64px;"></i>
    <h3 class="mt-3 fw-bold"><?= $pageTitle ?? '404 Not Found' ?></h3>
    <p>The page you're looking for doesn't exist or has been moved.</p>
    <a href="<?= Config::appUrl() ?>/dashboard" class="btn btn-primary mt-2"><i class="bi bi-house me-1"></i>Go to Dashboard</a>
</div>
