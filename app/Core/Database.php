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

                // Create tables for testing
                $this->createTestTables();
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

    /**
     * Create tables for SQLite testing
     */
    private function createTestTables() {
        $sql = "
        -- Administrators table
        CREATE TABLE IF NOT EXISTS administrators (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        -- Clients table
        CREATE TABLE IF NOT EXISTS clients (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE,
            phone VARCHAR(20),
            address TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        -- Movements table
        CREATE TABLE IF NOT EXISTS movements (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            client_id INTEGER NOT NULL,
            type VARCHAR(20) NOT NULL CHECK (type IN ('expense', 'earning', 'income')),
            amount DECIMAL(10,2) NOT NULL,
            description TEXT,
            date DATE NOT NULL,
            created_by INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
            FOREIGN KEY (created_by) REFERENCES administrators(id)
        );

        -- API tokens table
        CREATE TABLE IF NOT EXISTS api_tokens (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            administrator_id INTEGER NOT NULL,
            token_hash VARCHAR(64) NOT NULL UNIQUE,
            expires_at DATETIME NOT NULL,
            created_at DATETIME NOT NULL,
            last_used_at DATETIME,
            revoked_at DATETIME,
            user_agent TEXT,
            ip_address VARCHAR(45),
            FOREIGN KEY (administrator_id) REFERENCES administrators(id) ON DELETE CASCADE
        );
        ";

        // Execute each statement separately for SQLite
        $statements = array_filter(explode(';', $sql));
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                $this->connection->exec($statement);
            }
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

    /**
     * Reset instance (useful for testing)
     */
    public static function resetInstance() {
        self::$instance = null;
    }
}
