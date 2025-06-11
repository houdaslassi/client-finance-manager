-- Create database if not exists
CREATE DATABASE IF NOT EXISTS client_finance_manager;
USE client_finance_manager;

-- Administrators table
CREATE TABLE IF NOT EXISTS administrators (
                                              id INT AUTO_INCREMENT PRIMARY KEY,
                                              username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

-- Clients table
CREATE TABLE IF NOT EXISTS clients (
                                       id INT AUTO_INCREMENT PRIMARY KEY,
                                       name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

-- Financial movements table
CREATE TABLE IF NOT EXISTS movements (
                                         id INT AUTO_INCREMENT PRIMARY KEY,
                                         client_id INT NOT NULL,
                                         type ENUM('expense', 'earning') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description TEXT,
    date DATE NOT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES administrators(id)
    );

-- API tokens table for professional authentication
CREATE TABLE IF NOT EXISTS api_tokens (
                                          id INT AUTO_INCREMENT PRIMARY KEY,
                                          administrator_id INT NOT NULL,
                                          token_hash VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL,
    last_used_at DATETIME NULL,
    revoked_at DATETIME NULL,
    user_agent TEXT NULL,
    ip_address VARCHAR(45) NULL,
    INDEX idx_administrator_id (administrator_id),
    INDEX idx_token_hash (token_hash),
    INDEX idx_expires_at (expires_at),
    INDEX idx_active_tokens (administrator_id, expires_at, revoked_at),
    FOREIGN KEY (administrator_id) REFERENCES administrators(id) ON DELETE CASCADE
    );

-- Create indexes (compatible with older MySQL versions)
CREATE INDEX idx_client_movements ON movements(client_id, date);
CREATE INDEX idx_movement_type ON movements(type);

-- Insert default administrator (password: Admin123!)
INSERT IGNORE INTO administrators (username, email, password) VALUES (
    'admin',
    'admin@example.com',
    '$2y$10$7rLSvRVyTQORapkDOqmkhetjF6H9lJHBxgNvXqnEJALrS1bGiJDMW'
);
