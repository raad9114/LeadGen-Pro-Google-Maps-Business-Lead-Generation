<?php
/**
 * API Usage Controller
 */

class ApiUsageController
{
    public function index(): void
    {
        Session::requireAuth();
        $pageTitle = 'API Usage';
        $currentPage = 'api-usage';

        $apiLogModel = new ApiLog();
        $stats = $apiLogModel->getStats();
        $recentLogs = $apiLogModel->getRecent(100);

        include TEMPLATE_PATH . '/layouts/app.php';
    }

    public function stats(): void
    {
        Session::requireAuth();
        $apiLogModel = new ApiLog();
        Response::success($apiLogModel->getStats());
    }
}
