<?php

namespace Tests\Models;

use PHPUnit\Framework\TestCase;
use App\Models\Movement;
use App\Models\Client;

class MovementTest extends TestCase
{
    protected function setUp(): void
    {
        // Reset for fresh database connection
        \Core\Database::resetInstance();

        // Database class automatically creates tables via createTestTables()
        $pdo = \Core\Database::getInstance()->getConnection();

        // Just clean existing data for fresh test state
        $pdo->exec('DELETE FROM movements WHERE 1=1');
        $pdo->exec('DELETE FROM clients WHERE 1=1');
    }

    public function testCreateMovementWithValidData()
    {
        $client = new Client();
        $clientResult = $client->create([
            'name' => 'Test Client',
            'email' => 'test@client.com',
            'phone' => '1234567890'
        ]);

        // Handle different return formats (ID or array)
        $clientId = is_array($clientResult) ? $clientResult['id'] : $clientResult;

        $movement = new Movement();
        $result = $movement->create([
            'client_id' => $clientId,
            'type' => 'income',
            'amount' => 100.0,
            'date' => date('Y-m-d')
        ]);

        // Handle different return formats
        if (is_array($result)) {
            $this->assertTrue($result['success']);
        } else {
            $this->assertIsInt($result);
            $this->assertGreaterThan(0, $result);
        }
    }

    public function testCreateMovementWithInvalidType()
    {
        $client = new Client();
        $clientResult = $client->create([
            'name' => 'Test Client',
            'email' => 'test2@client.com',
            'phone' => '1234567890'
        ]);

        $clientId = is_array($clientResult) ? $clientResult['id'] : $clientResult;

        $movement = new Movement();
        $result = $movement->create([
            'client_id' => $clientId,
            'type' => 'invalid_type',
            'amount' => 50.0,
            'date' => date('Y-m-d')
        ]);

        // Movement model has validation, so this should return error array
        $this->assertIsArray($result);
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

        $clientId = is_array($clientResult) ? $clientResult['id'] : $clientResult;

        $movement = new Movement();
        $result = $movement->create([
            'client_id' => $clientId,
            'type' => 'expense'
            // 'amount' => missing
        ]);

        // Movement model has validation, so this should return error array
        $this->assertIsArray($result);
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

        $clientId = is_array($clientResult) ? $clientResult['id'] : $clientResult;

        $movement = new Movement();
        $movementResult = $movement->create([
            'client_id' => $clientId,
            'type' => 'income',
            'amount' => 200.0,
            'date' => date('Y-m-d')
        ]);

        // Verify movement was created
        if (is_array($movementResult)) {
            $this->assertTrue($movementResult['success']);
        } else {
            $this->assertIsInt($movementResult);
        }

        // Check relationship
        $movements = $movement->findAll();
        $this->assertCount(1, $movements);
        $this->assertEquals($clientId, $movements[0]['client_id']);
    }

    public function testMovementValidation()
    {
        $movement = new Movement();

        // Test validation directly
        $errors = $movement->validate([]);

        $this->assertArrayHasKey('client_id', $errors);
        $this->assertArrayHasKey('type', $errors);
        $this->assertArrayHasKey('amount', $errors);
    }

    public function testValidMovementTypes()
    {
        $client = new Client();
        $clientResult = $client->create([
            'name' => 'Test Client',
            'email' => 'validtypes@client.com',
            'phone' => '1234567890'
        ]);

        $clientId = is_array($clientResult) ? $clientResult['id'] : $clientResult;
        $movement = new Movement();

        // Test all valid types
        $validTypes = ['income', 'earning', 'expense'];

        foreach ($validTypes as $type) {
            $result = $movement->create([
                'client_id' => $clientId,
                'type' => $type,
                'amount' => 100.0,
                'date' => date('Y-m-d')
            ]);

            // Should succeed for all valid types
            if (is_array($result)) {
                $this->assertTrue($result['success'], "Type '{$type}' should be valid");
            } else {
                $this->assertIsInt($result, "Type '{$type}' should return valid ID");
            }
        }
    }

    public function testMovementAmountValidation()
    {
        $client = new Client();
        $clientResult = $client->create([
            'name' => 'Test Client',
            'email' => 'amount@client.com',
            'phone' => '1234567890'
        ]);

        $clientId = is_array($clientResult) ? $clientResult['id'] : $clientResult;
        $movement = new Movement();

        // Test negative amount
        $result = $movement->create([
            'client_id' => $clientId,
            'type' => 'income',
            'amount' => -50.0,
            'date' => date('Y-m-d')
        ]);

        $this->assertIsArray($result);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('amount', $result['errors']);

        // Test zero amount
        $result = $movement->create([
            'client_id' => $clientId,
            'type' => 'income',
            'amount' => 0,
            'date' => date('Y-m-d')
        ]);

        $this->assertIsArray($result);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('amount', $result['errors']);
    }
}
