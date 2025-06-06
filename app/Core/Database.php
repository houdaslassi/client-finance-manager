<?php
namespace Core;

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        // Try JawsDB URL first (Heroku)
        $jawsdb_url = getenv('JAWSDB_URL');
        
        if ($jawsdb_url) {
            $dbparts = parse_url($jawsdb_url);
            $host = $dbparts['host'];
            $user = $dbparts['user'];
            $pass = $dbparts['pass'];
            $dbname = ltrim($dbparts['path'], '/');
            $port = $dbparts['port'] ?? 3306;
            error_log("DB DEBUG: host=$host, port=$port, dbname=$dbname, user=$user");
        } else {
            // Fallback to config file for local development
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