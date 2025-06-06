<?php
namespace Core;

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        // Try environment variables first (Heroku)
        $host = getenv('DB_HOST');
        $dbname = getenv('DB_DATABASE');
        $user = getenv('DB_USERNAME');
        $pass = getenv('DB_PASSWORD');
        $port = getenv('DB_PORT') ?: 3306;

        // Fallback to config file for local development
        if (!$host || !$dbname || !$user) {
            $config = require __DIR__ . '/../../config/database.php';
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
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
        } catch (\PDOException $e) {
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