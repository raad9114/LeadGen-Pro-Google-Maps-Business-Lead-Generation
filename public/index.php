<?php
/**
 * LeadGen Pro — Front Controller
 * All requests route through this file
 */

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Define base paths
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('TEMPLATE_PATH', BASE_PATH . '/templates');
define('STORAGE_PATH', BASE_PATH . '/storage');

// Autoload helpers and config
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';
require_once CONFIG_PATH . '/routes.php';
require_once APP_PATH . '/helpers/Session.php';
require_once APP_PATH . '/helpers/CSRF.php';
require_once APP_PATH . '/helpers/Response.php';
require_once APP_PATH . '/helpers/Validator.php';

// Load configuration
Config::load();

// Set timezone
date_default_timezone_set(Config::get('APP_TIMEZONE', 'Asia/Dhaka'));

// Start session
Session::start();

// Simple PSR-4-like autoloader for models and services
spl_autoload_register(function (string $class) {
    $directories = [
        APP_PATH . '/models/',
        APP_PATH . '/services/',
        APP_PATH . '/helpers/',
        APP_PATH . '/controllers/',
    ];

    foreach ($directories as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Create log directory if it doesn't exist
if (!is_dir(STORAGE_PATH . '/logs')) {
    @mkdir(STORAGE_PATH . '/logs', 0755, true);
}

// ─── Check if installer needs to run ───
$installLockFile = BASE_PATH . '/install/.installed';
$url = trim($_GET['url'] ?? '', '/');

if (!file_exists($installLockFile) && !str_starts_with($url, 'install')) {
    header('Location: ' . Config::appUrl() . '/install');
    exit;
}

// ─── Define Routes ───
$router = new Router();

// Auth routes
$router->get('login', 'AuthController', 'showLogin');
$router->post('login', 'AuthController', 'login');
$router->get('logout', 'AuthController', 'logout');

// Dashboard
$router->get('', 'DashboardController', 'index');
$router->get('dashboard', 'DashboardController', 'index');

// Lead Search
$router->get('search', 'SearchController', 'index');
$router->post('api/search/start', 'SearchController', 'startSearch');
$router->get('api/search/status/{id}', 'SearchController', 'getStatus');
$router->get('api/search/results/{id}', 'SearchController', 'getResults');

// Leads
$router->get('leads', 'LeadController', 'index');
$router->get('api/leads', 'LeadController', 'list');
$router->get('api/leads/{id}', 'LeadController', 'show');
$router->get('leads/{id}', 'LeadController', 'detail');
$router->put('api/leads/{id}', 'LeadController', 'update');
$router->delete('api/leads/{id}', 'LeadController', 'delete');
$router->post('api/leads/{id}/find-email', 'LeadController', 'findEmail');
$router->post('api/leads/{id}/refresh', 'LeadController', 'refresh');
$router->post('api/leads/bulk-status', 'LeadController', 'bulkStatus');
$router->post('api/leads/bulk-email', 'LeadController', 'bulkEmail');
$router->post('api/leads/bulk-delete', 'LeadController', 'bulkDelete');
$router->post('api/leads/{id}/notes', 'LeadController', 'addNote');
$router->delete('api/leads/notes/{id}', 'LeadController', 'deleteNote');

// Search History
$router->get('history', 'SearchHistoryController', 'index');
$router->get('api/search-history', 'SearchHistoryController', 'list');
$router->delete('api/search-history/{id}', 'SearchHistoryController', 'delete');

// Categories
$router->get('categories', 'CategoryController', 'index');
$router->get('api/categories', 'CategoryController', 'list');
$router->post('api/categories', 'CategoryController', 'store');
$router->put('api/categories/{id}', 'CategoryController', 'update');
$router->delete('api/categories/{id}', 'CategoryController', 'delete');

// Locations
$router->get('locations', 'LocationController', 'index');
$router->get('api/countries', 'LocationController', 'countries');
$router->get('api/cities/{countryId}', 'LocationController', 'cities');
$router->get('api/areas/{cityId}', 'LocationController', 'areas');
$router->post('api/countries', 'LocationController', 'storeCountry');
$router->post('api/cities', 'LocationController', 'storeCity');
$router->post('api/areas', 'LocationController', 'storeArea');
$router->delete('api/countries/{id}', 'LocationController', 'deleteCountry');
$router->delete('api/cities/{id}', 'LocationController', 'deleteCity');
$router->delete('api/areas/{id}', 'LocationController', 'deleteArea');

// Export
$router->get('exports', 'ExportController', 'index');
$router->post('api/export/csv', 'ExportController', 'csv');
$router->post('api/export/xlsx', 'ExportController', 'xlsx');

// API Usage
$router->get('api-usage', 'ApiUsageController', 'index');
$router->get('api/usage-stats', 'ApiUsageController', 'stats');

// Users
$router->get('users', 'UserController', 'index');
$router->get('api/users', 'UserController', 'list');
$router->post('api/users', 'UserController', 'store');
$router->put('api/users/{id}', 'UserController', 'update');
$router->delete('api/users/{id}', 'UserController', 'delete');

// Settings
$router->get('settings', 'SettingsController', 'index');
$router->post('api/settings', 'SettingsController', 'save');

// Dashboard API
$router->get('api/dashboard/stats', 'DashboardController', 'stats');

// Installer
$router->get('install', 'InstallController', 'index');
$router->post('install', 'InstallController', 'install');

// Dispatch
$router->dispatch();
