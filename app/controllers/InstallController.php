<?php
/**
 * Install Controller
 */

class InstallController
{
    public function index(): void
    {
        // Check if already installed
        if (file_exists(BASE_PATH . '/install/.installed')) {
            echo '<h3>Application is already installed.</h3><p><a href="' . Config::appUrl() . '/login">Go to Login</a></p>';
            return;
        }

        $pageTitle = 'Install LeadGen Pro';
        $checks = $this->runChecks();

        include TEMPLATE_PATH . '/pages/install.php';
    }

    public function install(): void
    {
        if (file_exists(BASE_PATH . '/install/.installed')) {
            Response::error('Already installed');
            return;
        }

        $dbHost = trim($_POST['db_host'] ?? 'localhost');
        $dbPort = trim($_POST['db_port'] ?? '3306');
        $dbName = trim($_POST['db_name'] ?? 'lead_generation');
        $dbUser = trim($_POST['db_user'] ?? 'root');
        $dbPass = $_POST['db_pass'] ?? '';
        $adminUser = trim($_POST['admin_username'] ?? 'admin');
        $adminEmail = trim($_POST['admin_email'] ?? 'admin@leadgen.local');
        $adminPass = $_POST['admin_password'] ?? 'admin123';
        $apiKey = trim($_POST['api_key'] ?? '');

        try {
            // Test connection
            $pdo = Database::getRawConnection($dbHost, $dbPort, $dbUser, $dbPass);

            // Create database
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `{$dbName}`");

            // Run schema
            $schema = file_get_contents(BASE_PATH . '/database/schema.sql');
            $pdo->exec($schema);

            // Seed categories, countries, cities, areas, settings
            $seed = file_get_contents(BASE_PATH . '/database/seed.sql');

            // Remove the admin insert from seed — we'll create our own
            $seedLines = explode(';', $seed);
            foreach ($seedLines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                if (str_contains($line, "INSERT INTO `users`")) continue; // Skip default admin
                try {
                    $pdo->exec($line);
                } catch (\PDOException $e) {
                    // Ignore duplicate key errors during re-install
                    if ($e->getCode() != '23000') {
                        throw $e;
                    }
                }
            }

            // Create admin user
            $passwordHash = password_hash($adminPass, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $pdo->prepare(
                'INSERT INTO users (username, email, password_hash, full_name, role, status)
                 VALUES (?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), email = VALUES(email)'
            );
            $stmt->execute([$adminUser, $adminEmail, $passwordHash, 'Administrator', 'admin', 'active']);

            // Save API key if provided
            if (!empty($apiKey)) {
                $stmt = $pdo->prepare(
                    "INSERT INTO settings (setting_key, setting_value, setting_group)
                     VALUES ('google_places_api_key', ?, 'api')
                     ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
                );
                $stmt->execute([$apiKey]);
            }

            // Write .env file
            $envContent = "DB_HOST={$dbHost}\nDB_PORT={$dbPort}\nDB_NAME={$dbName}\nDB_USER={$dbUser}\nDB_PASS={$dbPass}\n";
            $envContent .= "GOOGLE_PLACES_API_KEY={$apiKey}\n";
            $envContent .= "APP_NAME=LeadGen Pro\n";
            $envContent .= "APP_URL=" . $this->detectAppUrl() . "\n";
            $envContent .= "APP_DEBUG=false\nAPP_TIMEZONE=Asia/Dhaka\n";
            $envContent .= "SESSION_LIFETIME=3600\nSESSION_NAME=leadgen_session\n";

            file_put_contents(BASE_PATH . '/.env', $envContent);

            // Create install lock
            if (!is_dir(BASE_PATH . '/install')) {
                mkdir(BASE_PATH . '/install', 0755, true);
            }
            file_put_contents(BASE_PATH . '/install/.installed', date('Y-m-d H:i:s'));

            // Create log directory
            if (!is_dir(STORAGE_PATH . '/logs')) {
                @mkdir(STORAGE_PATH . '/logs', 0755, true);
            }

            Session::flash('success', 'Installation completed successfully! Login with your admin credentials.');
            Response::redirect(Config::appUrl() . '/login');
        } catch (\Throwable $e) {
            $errorMsg = Config::isDebug() ? $e->getMessage() : 'Installation failed. Check your database credentials.';
            Session::flash('error', $errorMsg);
            Response::redirect(Config::appUrl() . '/install');
        }
    }

    private function runChecks(): array
    {
        return [
            'php_version' => [
                'label' => 'PHP 8.0+',
                'pass' => version_compare(PHP_VERSION, '8.0.0', '>='),
                'value' => PHP_VERSION,
            ],
            'pdo_mysql' => [
                'label' => 'PDO MySQL Extension',
                'pass' => extension_loaded('pdo_mysql'),
                'value' => extension_loaded('pdo_mysql') ? 'Loaded' : 'Missing',
            ],
            'curl' => [
                'label' => 'cURL Extension',
                'pass' => extension_loaded('curl'),
                'value' => extension_loaded('curl') ? 'Loaded' : 'Missing',
            ],
            'json' => [
                'label' => 'JSON Extension',
                'pass' => extension_loaded('json'),
                'value' => extension_loaded('json') ? 'Loaded' : 'Missing',
            ],
            'mbstring' => [
                'label' => 'Mbstring Extension',
                'pass' => extension_loaded('mbstring'),
                'value' => extension_loaded('mbstring') ? 'Loaded' : 'Missing',
            ],
            'zip' => [
                'label' => 'ZipArchive (XLSX export)',
                'pass' => class_exists('ZipArchive'),
                'value' => class_exists('ZipArchive') ? 'Available' : 'Missing (XLSX export disabled)',
            ],
            'writable_root' => [
                'label' => 'Root directory writable',
                'pass' => is_writable(BASE_PATH),
                'value' => is_writable(BASE_PATH) ? 'Writable' : 'Not Writable',
            ],
        ];
    }

    private function detectAppUrl(): string
    {
        $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptDir = dirname(dirname($_SERVER['SCRIPT_NAME']));
        return $scheme . '://' . $host . rtrim($scriptDir, '/');
    }
}
