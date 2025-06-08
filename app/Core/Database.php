<?php
namespace Core;

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        // Check for SQLite for testing
        $db_connection = getenv('DB_CONNECTION');
        $db_database = getenv('DB_DATABASE');
        if ($db_connection === 'sqlite' && $db_database) {
            try {
                $this->connection = new \PDO(
                    "sqlite:$db_database",
                    null,
                    null,
                    [
                        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                        \PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
                return;
            } catch (\PDOException $e) {
                error_log("SQLite connection failed: " . $e->getMessage());
                die("SQLite connection failed: " . $e->getMessage());
            }
        }

        // Try JawsDB URL first (Heroku)
        $jawsdb_url = getenv('JAWSDB_URL');

        if ($jawsdb_url) {
            $dbparts = parse_url($jawsdb_url);
            $host = $dbparts['host'];
            $user = $dbparts['user'];
            $pass = $dbparts['pass'];
            $dbname = ltrim($dbparts['path'], '/');
            $port = $dbparts['port'] ?? 3306;

            if (defined('APP_DEBUG') && APP_DEBUG) {
                error_log("DB DEBUG: host=$host, port=$port, dbname=$dbname, user=$user");
            }
        } else {
            // Use the unified config
            $config = require __DIR__ . '/../../config/app.php';
            $host = $config['host'];
            $dbname = $config['dbname'];
            $user = $config['user'];
            $pass = $config['pass'];
            $port = $config['port'] ?? 3306;
        }

        try {
            $this->connection = new \PDO(
                "mysql:host=$host;dbname=$dbname;port=$port;charset=utf8mb4",
                $user,
                $pass,
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (\PDOException $e) {
            error_log("Connection failed: " . $e->getMessage());
            die("Connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }
}
