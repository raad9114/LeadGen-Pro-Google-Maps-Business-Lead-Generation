<?php
/**
 * Search Controller
 */

class SearchController
{
    public function index(): void
    {
        Session::requireAuth();

        $pageTitle = 'Lead Search';
        $currentPage = 'search';

        $categoryModel = new Category();
        $locationModel = new Location();
        $settingModel = new Setting();

        $categories = $categoryModel->getAll();
        $countries = $locationModel->getCountries();
        $defaultCountry = $settingModel->get('default_country', 'Bangladesh');
        $maxResults = $settingModel->get('max_results_per_search', '60');

        include TEMPLATE_PATH . '/layouts/app.php';
    }

    public function startSearch(): void
    {
        Session::requireAuth();
        CSRF::validateOrDie();

        $category = trim($_POST['category'] ?? '');
        $customCategory = trim($_POST['custom_category'] ?? '');
        $country = trim($_POST['country'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $area = trim($_POST['area'] ?? '');
        $customLocation = trim($_POST['custom_location'] ?? '');
        $maxResults = (int) ($_POST['max_results'] ?? 60);
        $searchMode = $_POST['search_mode'] ?? 'text';
        $latitude = $_POST['latitude'] ?? null;
        $longitude = $_POST['longitude'] ?? null;
        $radiusKm = $_POST['radius_km'] ?? null;

        // Use custom category if provided
        $categoryName = !empty($customCategory) ? $customCategory : $category;

        if (empty($categoryName)) {
            Response::error('Business category is required.');
            return;
        }

        $maxResults = max(1, min($maxResults, 200));

        $searchService = new SearchService();
        $result = $searchService->startSearch([
            'user_id' => Session::userId(),
            'category' => $categoryName,
            'country' => $country,
            'city' => $city,
            'area' => $area,
            'custom_location' => $customLocation,
            'max_results' => $maxResults,
            'search_mode' => $searchMode,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'radius_km' => $radiusKm,
        ]);

        if (isset($result['error'])) {
            Response::error($result['error']);
            return;
        }

        // Execute search synchronously (for progress tracking via polling)
        $execResult = $searchService->executeSearch($result['job_id']);

        if (isset($execResult['error'])) {
            Response::error($execResult['error']);
            return;
        }

        Response::success([
            'job_id' => $result['job_id'],
            'query' => $result['query'],
            'stats' => $execResult['stats'] ?? [],
        ], 'Search completed successfully');
    }

    public function getStatus(string $id): void
    {
        Session::requireAuth();

        $searchJobModel = new SearchJob();
        $job = $searchJobModel->findById((int) $id);

        if (!$job) {
            Response::error('Search job not found', 404);
            return;
        }

        Response::success($job);
    }

    public function getResults(string $id): void
    {
        Session::requireAuth();

        $leadModel = new Lead();
        $result = $leadModel->list(['search_job_id' => (int) $id], 1, 200);

        Response::success($result);
    }
}
