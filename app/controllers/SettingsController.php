<?php
/**
 * Settings Controller
 */

class SettingsController
{
    public function index(): void
    {
        Session::requireAdmin();
        $pageTitle = 'Settings';
        $currentPage = 'settings';

        $settingModel = new Setting();
        $settings = [];
        foreach ($settingModel->getAll() as $s) {
            $settings[$s['setting_key']] = $s['setting_value'];
        }

        include TEMPLATE_PATH . '/layouts/app.php';
    }

    public function save(): void
    {
        Session::requireAdmin();
        CSRF::validateOrDie();

        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $settingModel = new Setting();

        $allowedSettings = [
            'google_places_api_key' => 'api',
            'google_maps_js_api_key' => 'api',
            'max_results_per_search' => 'search',
            'max_pages_to_crawl' => 'email',
            'crawl_timeout_seconds' => 'email',
            'requests_per_minute' => 'api',
            'enable_email_enrichment' => 'email',
            'auto_search_emails' => 'email',
            'default_country' => 'search',
            'cost_per_text_search' => 'api',
            'cost_per_place_details' => 'api',
        ];

        foreach ($allowedSettings as $key => $group) {
            if (array_key_exists($key, $data)) {
                $settingModel->set($key, $data[$key], $group);
            }
        }

        Response::success(null, 'Settings saved successfully');
    }
}
