<?php

namespace Tests\Controllers\API;

use PHPUnit\Framework\TestCase;
use App\Controllers\API\AuthAPIController;
use App\Models\Administrator;

class AuthAPIControllerTest extends TestCase
{
    protected $authController;
    protected $pdo;

    protected function setUp(): void
    {
        // Reset database connection
        \Core\Database::resetInstance();
        $this->pdo = \Core\Database::getInstance()->getConnection();

        // Create required tables
        $this->createTestTables();

        // Clean existing data
        $this->pdo->exec('DELETE FROM api_tokens WHERE 1=1');
        $this->pdo->exec('DELETE FROM administrators WHERE 1=1');

        // Create test admin
        $this->createTestAdmin();

        // Mock global variables and functions for testing
        $this->mockGlobalEnvironment();
    }

    protected function createTestTables()
    {
        // Tables are automatically created by Database::createTestTables()
        // No need to recreate them here since Database singleton handles this
    }

    protected function createTestAdmin()
    {
        $strongPassword = 'TestPass123!';
        $hash = password_hash($strongPassword, PASSWORD_DEFAULT);
        $this->pdo->exec("INSERT INTO administrators (username, email, password) VALUES (
            'testadmin',
            'admin@test.com',
            '" . $hash . "'
        )");
    }

    protected function mockGlobalEnvironment()
    {
        // Mock $_SERVER variables
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTP_USER_AGENT'] = 'PHPUnit Test';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        // Mock headers function only if it doesn't exist
        if (!function_exists('getallheaders')) {
            eval('function getallheaders() { return []; }');
        }

        // Capture output to prevent headers/output during tests
        if (!headers_sent()) {
            ob_start();
        }
    }

    protected function tearDown(): void
    {
        // Clean up output buffer
        if (ob_get_level()) {
            ob_end_clean();
        }
    }

    public function testLoginWithValidCredentials()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        ob_start();
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        $jsonInput = json_encode([
            'username' => 'testadmin',
            'password' => 'TestPass123!'
        ]);
        try {
            $controller = $this->createMockAuthController($jsonInput);
            $controller->login();
        } catch (\Throwable $e) {}
        $output = ob_get_clean();
        $lines = explode("\n", $output);
        $jsonLine = '';
        foreach ($lines as $line) {
            if (strpos($line, '{') === 0) {
                $jsonLine = $line;
                break;
            }
        }
        if ($jsonLine) {
            $response = json_decode($jsonLine, true);
            if ($response && isset($response['success']) && $response['success']) {
                $this->assertTrue(true, "Login successful");
                return;
            }
        }
        $this->fail("Login failed - see output above");
    }

    public function testLoginWithInvalidCredentials()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $jsonInput = json_encode([
            'username' => 'testadmin',
            'password' => 'WrongPass123!'
        ]);

        $controller = $this->createMockAuthController($jsonInput);

        ob_start();
        try {
            $controller->login();
        } catch (\Exception $e) {
            // Expected to exit
        }

        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertFalse($response['success']);
        $this->assertEquals('Invalid credentials', $response['error']);
    }

    public function testLoginWithMissingCredentials()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $jsonInput = json_encode([
            'username' => 'testadmin'
            // Missing password
        ]);

        $controller = $this->createMockAuthController($jsonInput);

        ob_start();
        try {
            $controller->login();
        } catch (\Exception $e) {
            // Expected to exit
        }

        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertFalse($response['success']);
        $this->assertEquals('Username and password are required', $response['error']);
        $this->assertArrayHasKey('required_fields', $response['details']);
    }

    public function testLoginWithInvalidHttpMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $controller = new AuthAPIController();

        ob_start();
        try {
            $controller->login();
        } catch (\Exception $e) {
            // Expected to exit
        }

        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('Method not allowed', $response['error']);
    }

    public function testMeEndpointWithValidToken()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        // First create a token by logging in
        $token = $this->createValidToken();

        // Set the authorization header
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;

        $controller = new AuthAPIController();

        ob_start();
        try {
            $controller->me();
        } catch (\Exception $e) {
            // Expected to exit
        }

        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('admin', $response['data']);
        $this->assertArrayHasKey('token_info', $response['data']);
        $this->assertEquals('testadmin', $response['data']['admin']['username']);
    }

    public function testMeEndpointWithInvalidToken()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer invalid_token';

        $controller = new AuthAPIController();

        ob_start();
        try {
            $controller->me();
        } catch (\Exception $e) {
            // Expected to exit
        }

        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertFalse($response['success']);
        $this->assertEquals('Invalid or expired token', $response['error']);
    }

    public function testLogoutWithValidToken()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $token = $this->createValidToken();
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;

        $controller = new AuthAPIController();

        ob_start();
        try {
            $controller->logout();
        } catch (\Exception $e) {
            // Expected to exit
        }

        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertTrue($response['success']);
        $this->assertEquals('Successfully logged out', $response['message']);

        // Verify token is revoked
        $stmt = $this->pdo->prepare("SELECT revoked_at FROM api_tokens WHERE token_hash = ?");
        $stmt->execute([hash('sha256', $token)]);
        $result = $stmt->fetch();
        $this->assertNotNull($result['revoked_at']);
    }

    public function testLogoutAllTokens()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        // Create multiple tokens for the same admin
        $token1 = $this->createValidToken();
        $token2 = $this->createValidToken();

        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token1;

        $controller = new AuthAPIController();

        ob_start();
        try {
            $controller->logoutAll();
        } catch (\Exception $e) {
            // Expected to exit
        }

        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertTrue($response['success']);
        $this->assertEquals('All sessions logged out successfully', $response['message']);

        // Verify all tokens are revoked
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM api_tokens WHERE administrator_id = 1 AND revoked_at IS NULL");
        $stmt->execute();
        $activeTokens = $stmt->fetchColumn();
        $this->assertEquals(0, $activeTokens);
    }

    public function testTokenExpirationHandling()
    {
        // Create an expired token
        $expiredToken = bin2hex(random_bytes(32));
        $this->pdo->exec("INSERT INTO api_tokens (administrator_id, token_hash, expires_at, created_at, last_used_at) VALUES (
            1,
            '" . hash('sha256', $expiredToken) . "',
            datetime('now', '-1 day'),
            datetime('now', '-2 days'),
            datetime('now', '-1 day')
        )");

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $expiredToken;

        $controller = new AuthAPIController();

        ob_start();
        try {
            $controller->me();
        } catch (\Exception $e) {
            // Expected to exit
        }

        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertFalse($response['success']);
        $this->assertEquals('Invalid or expired token', $response['error']);
    }

    public function testTokenLimitEnforcement()
    {
        // Create 5 tokens (which should be the limit)
        for ($i = 0; $i < 6; $i++) {
            $this->createValidToken();
        }

        // Check that only 5 active tokens exist
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM api_tokens WHERE administrator_id = 1 AND revoked_at IS NULL AND expires_at > datetime('now')");
        $stmt->execute();
        $activeTokens = $stmt->fetchColumn();

        $this->assertLessThanOrEqual(5, $activeTokens);
    }

    /**
     * Helper method to create a valid token for testing
     */
    private function createValidToken()
    {
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $this->pdo->exec("INSERT INTO api_tokens (administrator_id, token_hash, expires_at, created_at, last_used_at) VALUES (
            1,
            '" . hash('sha256', $token) . "',
            '$expiresAt',
            datetime('now'),
            datetime('now')
        )");

        return $token;
    }

    /**
     * Create a mock controller that can handle JSON input
     */
    private function createMockAuthController($jsonInput)
    {
        // Create a mock that overrides the getJSONInput method
        $controller = new class($jsonInput) extends AuthAPIController {
            private $mockInput;

            public function __construct($jsonInput) {
                parent::__construct();
                $this->mockInput = $jsonInput;
            }

            protected function getJSONInput() {
                $data = json_decode($this->mockInput, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->apiError('Invalid JSON in request body: ' . json_last_error_msg(), 400);
                }
                return $data;
            }
        };

        return $controller;
    }
}
