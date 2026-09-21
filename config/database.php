<?php
/**
 * Database Connection (PDO Singleton)
 */

class Database
{
    private static ?PDO $instance = null;

    /**
     * Get PDO connection instance
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $db = Config::database();

            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $db['host'],
                $db['port'],
                $db['name']
            );

            try {
                self::$instance = new PDO($dsn, $db['user'], $db['pass'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                ]);
            } catch (PDOException $e) {
                if (Config::isDebug()) {
                    throw $e;
                }
                error_log('Database connection failed: ' . $e->getMessage());
                http_response_code(500);
                echo json_encode(['error' => 'Database connection failed']);
                exit;
            }
        }

        return self::$instance;
    }

    /**
     * Get a raw PDO connection (for installer — no database selected)
     */
    public static function getRawConnection(string $host, string $port, string $user, string $pass): PDO
    {
        $dsn = sprintf('mysql:host=%s;port=%s;charset=utf8mb4', $host, $port);
        return new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
}
