<?php
/**
 * Configuration Loader
 * Reads .env file and provides config access
 */

class Config
{
    private static array $config = [];
    private static bool $loaded = false;

    /**
     * Load configuration from .env file
     */
    public static function load(): void
    {
        if (self::$loaded) return;

        $envFile = dirname(__DIR__) . '/.env';
        if (!file_exists($envFile)) {
            $envFile = dirname(__DIR__) . '/.env.example';
        }

        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || str_starts_with($line, '#')) continue;
                if (!str_contains($line, '=')) continue;

                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Remove surrounding quotes
                if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                    (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                    $value = substr($value, 1, -1);
                }

                self::$config[$key] = $value;
                if (!isset($_ENV[$key])) {
                    $_ENV[$key] = $value;
                }
            }
        }

        self::$loaded = true;
    }

    /**
     * Get a config value
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        self::load();
        return self::$config[$key] ?? $_ENV[$key] ?? $default;
    }

    /**
     * Get database config array
     */
    public static function database(): array
    {
        return [
            'host' => self::get('DB_HOST', 'localhost'),
            'port' => self::get('DB_PORT', '3306'),
            'name' => self::get('DB_NAME', 'lead_generation'),
            'user' => self::get('DB_USER', 'root'),
            'pass' => self::get('DB_PASS', ''),
        ];
    }

    /**
     * Get app base URL
     */
    public static function appUrl(): string
    {
        return rtrim(self::get('APP_URL', ''), '/');
    }

    /**
     * Get app name
     */
    public static function appName(): string
    {
        return self::get('APP_NAME', 'LeadGen Pro');
    }

    /**
     * Check if debug mode is enabled
     */
    public static function isDebug(): bool
    {
        return filter_var(self::get('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN);
    }
}
