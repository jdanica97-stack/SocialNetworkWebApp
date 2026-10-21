<?php
// config/database.php
// Database configuration and PDO connection for SMCC Connect

class Database {
    private static $host = '127.0.0.1';
    private static $port = '3306';
    private static $db_name = 'social_app';
    private static $username = 'root';
    private static $password = '';
    private static $charset = 'utf8mb4';
    private static $conn = null;

    /**
     * Get the PDO database connection.
     *
     * @return PDO
     */
    public static function connect() {
        if (self::$conn === null) {
            $host = getenv('DB_HOST') ?: self::$host;
            $port = getenv('DB_PORT') ?: self::$port;
            $dbName = getenv('DB_NAME') ?: (defined('DB_NAME') ? DB_NAME : self::$db_name);
            $username = getenv('DB_USER') ?: self::$username;
            $password = (getenv('DB_PASS') !== false) ? getenv('DB_PASS') : self::$password;
            $charset = self::$charset;

            $dsn = "mysql:host=" . $host . ";port=" . $port . ";dbname=" . $dbName . ";charset=" . $charset;

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$conn = new PDO($dsn, $username, $password, $options);
                self::$conn->exec("SET time_zone = '+08:00';");
            } catch (PDOException $e) {
                // Log actual error securely to server log without exposing details to users
                error_log("Database connection error: " . $e->getMessage());
                if (class_exists('ErrorHandler')) {
                    ErrorHandler::renderErrorPage(500, 'Database Unavailable', 'The database is temporarily unavailable. Please try again later.');
                    exit;
                }
                die("Database connection failed. Please try again later.");
            }
        }

        return self::$conn;
    }

    /**
     * Inject a test or mock PDO instance (for automated testing).
     */
    public static function setConnection(?PDO $conn): void {
        self::$conn = $conn;
    }

    /**
     * Disconnect and clear the cached PDO connection.
     */
    public static function disconnect(): void {
        self::$conn = null;
    }
}

// Establish and provide the connection instance
$pdo = Database::connect();
