<?php

namespace Tests\Models;

use PHPUnit\Framework\TestCase;
use App\Models\Movement;
use App\Models\Client;

class MovementTest extends TestCase
{
    protected function setUp(): void
    {
        $pdo = \Core\Database::getInstance()->getConnection();
        $pdo->exec('CREATE TABLE IF NOT EXISTS clients (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            phone TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )');
        $pdo->exec('CREATE TABLE IF NOT EXISTS movements (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            client_id INTEGER NOT NULL,
            type TEXT NOT NULL,
            amount REAL NOT NULL,
            date DATE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (client_id) REFERENCES clients(id)
        )');
        $pdo->exec('DELETE FROM movements');
        $pdo->exec('DELETE FROM clients');
    }

    public function testCreateMovementWithValidData()
    {
        $client = new Client();
        $clientResult = $client->create([
            'name' => 'Test Client',
            'email' => 'test@client.com',
            'phone' => '1234567890'
        ]);
        $clientId = $clientResult['id'];

        $movement = new Movement();
        $result = $movement->create([
            'client_id' => $clientId,
            'type' => 'income',
            'amount' => 100.0,
            'date' => date('Y-m-d')
        ]);
        $this->assertTrue($result['success']);
    }

    public function testCreateMovementWithInvalidType()
    {
        $client = new Client();
        $clientResult = $client->create([
            'name' => 'Test Client',
            'email' => 'test2@client.com',
            'phone' => '1234567890'
        ]);
        $clientId = $clientResult['id'];

        $movement = new Movement();
        $result = $movement->create([
            'client_id' => $clientId,
            'type' => 'invalid_type',
            'amount' => 50.0,
            'date' => date('Y-m-d')
        ]);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('type', $result['errors']);
    }

    public function testCreateMovementWithMissingAmount()
    {
        $client = new Client();
        $clientResult = $client->create([
            'name' => 'Test Client',
            'email' => 'test3@client.com',
            'phone' => '1234567890'
        ]);
        $clientId = $clientResult['id'];

        $movement = new Movement();
        $result = $movement->create([
            'client_id' => $clientId,
            'type' => 'expense',
            // 'amount' => missing
            'date' => date('Y-m-d')
        ]);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('amount', $result['errors']);
    }

    public function testMovementLinkedToClient()
    {
        $client = new Client();
        $clientResult = $client->create([
            'name' => 'Test Client',
            'email' => 'test4@client.com',
            'phone' => '1234567890'
        ]);
        $clientId = $clientResult['id'];

        $movement = new Movement();
        $movement->create([
            'client_id' => $clientId,
            'type' => 'income',
            'amount' => 200.0,
            'date' => date('Y-m-d')
        ]);
        $movements = $movement->findAll();
        $this->assertCount(1, $movements);
        $this->assertEquals($clientId, $movements[0]['client_id']);
    }
} 