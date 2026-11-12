<?php
declare(strict_types=1);

/**
 * Waypure Singleton PDO Database Connection Class
 * Pure Core PHP 8.4+ Architecture
 */

require_once __DIR__ . '/config.php';

final class Database {
    private const string DB_HOST = 'localhost';
    private const string DB_USER = 'root';
    private const string DB_PASS = '';
    private const string DB_NAME = 'waypure_db';
    private const string DB_CHARSET = 'utf8mb4';

    private static ?PDO $instance = null;

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {}

    /**
     * Prevent object cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialization
     */
    public function __wakeup(): void {
        throw new \Exception("Cannot unserialize singleton");
    }

    /**
     * Retrieve Singleton PDO Instance
     */
    public static function getInstance(): PDO {
        if (self::$instance !== null) {
            return self::$instance;
        }

        try {
            // First check if database exists, create if not
            $initDsn = sprintf('mysql:host=%s;charset=%s', self::DB_HOST, self::DB_CHARSET);
            $initPdo = new PDO($initDsn, self::DB_USER, self::DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            $initPdo->exec(sprintf(
                'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;',
                self::DB_NAME
            ));
            unset($initPdo);

            // Connect to the specific database
            $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', self::DB_HOST, self::DB_NAME, self::DB_CHARSET);
            self::$instance = new PDO($dsn, self::DB_USER, self::DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            return self::$instance;
        } catch (PDOException $e) {
            error_log('[Waypure Database Error] ' . $e->getMessage());
            die('Database Connection Failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
        }
    }
}

/**
 * Global helper function to get PDO database instance
 */
function getDb(): PDO {
    return Database::getInstance();
}

