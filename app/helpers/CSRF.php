<?php
/**
 * CSRF Protection Helper
 */

class CSRF
{
    private static string $tokenName = '_csrf_token';

    /**
     * Generate and store CSRF token
     */
    public static function generate(): string
    {
        if (!isset($_SESSION[self::$tokenName])) {
            $_SESSION[self::$tokenName] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::$tokenName];
    }

    /**
     * Get hidden input field HTML
     */
    public static function field(): string
    {
        $token = self::generate();
        return '<input type="hidden" name="' . self::$tokenName . '" value="' . htmlspecialchars($token) . '">';
    }

    /**
     * Get token value for AJAX headers
     */
    public static function token(): string
    {
        return self::generate();
    }

    /**
     * Validate the CSRF token from request
     */
    public static function validate(): bool
    {
        $token = $_POST[self::$tokenName]
            ?? $_SERVER['HTTP_X_CSRF_TOKEN']
            ?? '';

        if (empty($token) || !isset($_SESSION[self::$tokenName])) {
            return false;
        }

        return hash_equals($_SESSION[self::$tokenName], $token);
    }

    /**
     * Validate and abort if invalid
     */
    public static function validateOrDie(): void
    {
        if (!self::validate()) {
            http_response_code(403);
            if (self::isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Invalid CSRF token. Please refresh the page.']);
            } else {
                echo 'Invalid CSRF token. Please refresh the page.';
            }
            exit;
        }
    }

    /**
     * Regenerate CSRF token
     */
    public static function regenerate(): string
    {
        $_SESSION[self::$tokenName] = bin2hex(random_bytes(32));
        return $_SESSION[self::$tokenName];
    }

    private static function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
