<?php

namespace Tests\Models;

use PHPUnit\Framework\TestCase;
use App\Models\Client;

class ClientTest extends TestCase
{
    protected function setUp(): void
    {
        // Adjust the namespace/class if your Database class is elsewhere
        $pdo = \Core\Database::getInstance()->getConnection();
        $pdo->exec('CREATE TABLE IF NOT EXISTS clients (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            phone TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )');
        $pdo->exec('DELETE FROM clients');
    }

    public function testCreateClientWithValidData()
    {
        $client = new Client();
        $result = $client->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890'
        ]);
        $this->assertTrue($result['success']);
    }

    public function testCreateClientWithDuplicateEmail()
    {
        $client = new Client();
        // Insert the first client
        $client->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890'
        ]);
        // Attempt to insert duplicate
        $result = $client->create([
            'name' => 'Jane Doe',
            'email' => 'john@example.com', // Duplicate email
            'phone' => '0987654321'
        ]);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('email', $result['errors']);
    }

    public function testCreateTwoClientsWithDifferentEmails()
    {
        $client = new Client();
        $result1 = $client->create([
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'phone' => '1111111111'
        ]);
        $result2 = $client->create([
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'phone' => '2222222222'
        ]);
        $this->assertTrue($result1['success']);
        $this->assertTrue($result2['success']);
    }
} 