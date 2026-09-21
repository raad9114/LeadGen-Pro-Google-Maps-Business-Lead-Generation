<?php
/**
 * Dashboard Controller
 */

class DashboardController
{
    public function index(): void
    {
        Session::requireAuth();

        $pageTitle = 'Dashboard';
        $currentPage = 'dashboard';

        $leadModel = new Lead();
        $searchJobModel = new SearchJob();
        $apiLogModel = new ApiLog();

        $leadStats = $leadModel->getStats();
        $recentSearches = $searchJobModel->getRecent(5);
        $recentLeads = $leadModel->getRecent(10);
        $apiStats = $apiLogModel->getStats();
        $byCategory = $leadModel->getByCategory(8);
        $byArea = $leadModel->getByArea(8);

        // Email enrichment rate
        $totalWithWebsite = $leadStats['with_website'] ?? 0;
        $totalWithEmail = $leadStats['with_email'] ?? 0;
        $emailRate = $totalWithWebsite > 0 ? round(($totalWithEmail / $totalWithWebsite) * 100, 1) : 0;

        include TEMPLATE_PATH . '/layouts/app.php';
    }

    public function stats(): void
    {
        Session::requireAuth();

        $leadModel = new Lead();
        $searchJobModel = new SearchJob();
        $apiLogModel = new ApiLog();

        Response::success([
            'leads' => $leadModel->getStats(),
            'searches_today' => $searchJobModel->getTodayCount(),
            'api' => $apiLogModel->getStats(),
        ]);
    }
}
