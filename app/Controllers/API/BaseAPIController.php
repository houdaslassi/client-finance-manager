<?php
namespace App\Controllers\API;

use Core\Database;

class BaseAPIController {
    protected $db;
    protected $validTokens = [
        'cfm_admin_2025' => 'admin',
        'cfm_demo_2025' => 'demo'
    ];

    public function __construct() {
        // Reuse the same database singleton as web controllers
        $this->db = Database::getInstance()->getConnection();
        $this->setAPIHeaders();
    }

    /**
     * Set JSON and CORS headers for API responses
     */
    private function setAPIHeaders() {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-API-Token, Authorization');

        // Handle preflight OPTIONS requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }

    /**
     * API Token Authentication (updated to use database)
     * @return array Admin data
     */
    protected function authenticateAPI() {
        $token = $this->getAPIToken();

        if (!$token) {
            $this->apiError('Authorization token required', 401, [
                'hint' => 'Include token in Authorization header: Bearer <token>'
            ]);
        }

        // Check token in database
        $stmt = $this->db->prepare("
            SELECT 
                at.expires_at,
                at.last_used_at,
                a.id, 
                a.username, 
                a.email 
            FROM api_tokens at 
            JOIN administrators a ON at.administrator_id = a.id 
            WHERE at.token_hash = ? 
            AND at.expires_at > NOW() 
            AND at.revoked_at IS NULL
        ");

        $stmt->execute([hash('sha256', $token)]);
        $result = $stmt->fetch();

        if (!$result) {
            $this->apiError('Invalid or expired token', 401);
        }

        // Update last used timestamp
        $updateStmt = $this->db->prepare("
            UPDATE api_tokens 
            SET last_used_at = NOW() 
            WHERE token_hash = ?
        ");
        $updateStmt->execute([hash('sha256', $token)]);

        return [
            'id' => $result['id'],
            'username' => $result['username'],
            'email' => $result['email'],
            'token_expires' => $result['expires_at']
        ];
    }

    /**
     * Get API token from multiple sources
     */
    private function getAPIToken() {
        return $_GET['api_token'] ??
            $_SERVER['HTTP_X_API_TOKEN'] ??
            $this->extractBearerToken();
    }

    /**
     * Extract Bearer token from Authorization header
     */
    private function extractBearerToken() {
        $headers = getallheaders();
        if (isset($headers['Authorization']) && strpos($headers['Authorization'], 'Bearer ') === 0) {
            return substr($headers['Authorization'], 7); // Remove 'Bearer ' prefix
        }
        return null;
    }

    /**
     * Validate HTTP method for API endpoint
     */
    protected function validateAPIMethod(array $allowedMethods = ['GET']) {
        $currentMethod = $_SERVER['REQUEST_METHOD'];

        if (!in_array($currentMethod, $allowedMethods)) {
            $this->apiError(
                'Method not allowed. Allowed methods: ' . implode(', ', $allowedMethods),
                405
            );
        }
    }

    /**
     * Validate required parameters exist
     */
    protected function validateRequiredParams(array $required, array $source = null) {
        $source = $source ?? $_GET;
        $missing = [];

        foreach ($required as $param) {
            if (!isset($source[$param]) || $source[$param] === '') {
                $missing[] = $param;
            }
        }

        if (!empty($missing)) {
            $this->apiError(
                'Missing required parameters: ' . implode(', ', $missing),
                400,
                ['required_params' => $required, 'missing' => $missing]
            );
        }
    }

    /**
     * Get and validate JSON input from request body
     */
    protected function getJSONInput() {
        $rawInput = file_get_contents('php://input');

        if (empty($rawInput)) {
            $this->apiError('Request body is required', 400);
        }

        $data = json_decode($rawInput, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->apiError(
                'Invalid JSON in request body: ' . json_last_error_msg(),
                400
            );
        }

        return $data;
    }

    /**
     * Send successful API response
     */
    protected function apiSuccess(array $data = [], string $message = null, int $httpCode = 200) {
        http_response_code($httpCode);

        $response = ['success' => true];

        if ($message) {
            $response['message'] = $message;
        }

        if (!empty($data)) {
            $response['data'] = $data;
        }

        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Send error API response
     */
    protected function apiError(string $message, int $httpCode = 400, array $details = []) {
        http_response_code($httpCode);

        $response = [
            'success' => false,
            'error' => $message
        ];

        if (!empty($details)) {
            $response['details'] = $details;
        }

        // Add debug info in development
        if (defined('APP_DEBUG') && APP_DEBUG && $httpCode >= 500) {
            $response['debug'] = [
                'file' => debug_backtrace()[1]['file'] ?? 'unknown',
                'line' => debug_backtrace()[1]['line'] ?? 'unknown'
            ];
        }

        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Log API activity for debugging (optional)
     */
    protected function logAPIActivity(string $action, array $context = []) {
        if (defined('APP_DEBUG') && APP_DEBUG) {
            $logData = [
                'timestamp' => date('Y-m-d H:i:s'),
                'action' => $action,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'context' => $context
            ];

            error_log('API_LOG: ' . json_encode($logData));
        }
    }

    /**
     * Validate that a model record exists
     */
    protected function validateRecordExists($model, $id, string $recordType = 'Record') {
        $record = $model->find($id);

        if (!$record) {
            $this->apiError(
                "{$recordType} not found",
                404,
                ["{$recordType}_id" => $id]
            );
        }

        return $record;
    }

    /**
     * Sanitize and validate numeric input
     */
    protected function validatePositiveNumber($value, string $fieldName) {
        if (!is_numeric($value) || $value <= 0) {
            $this->apiError("{$fieldName} must be a positive number", 400);
        }

        return (float) $value;
    }

    /**
     * Validate date format (YYYY-MM-DD)
     */
    protected function validateDateFormat($date, string $fieldName) {
        if ($date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $this->apiError("{$fieldName} must be in YYYY-MM-DD format", 400);
        }

        return $date;
    }
}
