<?php
/**
 * Search History Controller
 */

class SearchHistoryController
{
    public function index(): void
    {
        Session::requireAuth();
        $pageTitle = 'Search History';
        $currentPage = 'history';

        include TEMPLATE_PATH . '/layouts/app.php';
    }

    public function list(): void
    {
        Session::requireAuth();

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = max(1, min(50, (int) ($_GET['per_page'] ?? 20)));

        $searchJobModel = new SearchJob();
        $result = $searchJobModel->getAll($page, $perPage);

        Response::paginated($result['data'], $result['total'], $page, $perPage);
    }

    public function delete(string $id): void
    {
        Session::requireAdmin();
        CSRF::validateOrDie();

        $searchJobModel = new SearchJob();
        $searchJobModel->delete((int) $id);
        Response::success(null, 'Search deleted');
    }
}
