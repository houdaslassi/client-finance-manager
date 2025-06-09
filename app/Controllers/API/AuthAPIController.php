<?php
namespace App\Controllers\API;

use App\Models\Administrator;

class AuthAPIController extends BaseAPIController {

    /**
     * API Login - Get access token
     * POST /api/auth/login
     */
    public function login() {
        $this->validateAPIMethod(['POST']);

        $input = $this->getJSONInput();

        // Validate credentials
        if (!isset($input['username']) || !isset($input['password'])) {
            $this->apiError('Username and password are required', 400, [
                'required_fields' => ['username', 'password']
            ]);
        }

        try {
            // Authenticate admin
            $adminModel = new Administrator();
            $admin = $adminModel->authenticate($input['username'], $input['password']);

            if (!$admin) {
                $this->logAPIActivity('login_failed', [
                    'username' => $input['username'],
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]);

                // Add delay to prevent brute force attacks
                sleep(1);
                $this->apiError('Invalid credentials', 401);
            }

            // Generate secure token
            $token = $this->generateSecureToken();
            $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

            // Store token in database with metadata
            $this->storeAPIToken(
                $admin['id'],
                $token,
                $expiresAt,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
                $_SERVER['REMOTE_ADDR'] ?? null
            );

            $this->logAPIActivity('login_success', [
                'administrator_id' => $admin['id'],
                'username' => $admin['username']
            ]);

            $this->apiSuccess([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => 86400, // 24 hours
                'expires_at' => $expiresAt,
                'admin' => [
                    'id' => $admin['id'],
                    'username' => $admin['username'],
                    'email' => $admin['email']
                ]
            ], 'Authentication successful');

        } catch (\Exception $e) {
            $this->logAPIActivity('login_error', [
                'username' => $input['username'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            $this->apiError('Authentication failed', 500);
        }
    }

    /**
     * API Logout - Revoke current token
     * POST /api/auth/logout
     */
    public function logout() {
        $this->validateAPIMethod(['POST']);

        $admin = $this->authenticateAPI(true);

        try {
            // Revoke current token
            $this->revokeAPIToken($admin['current_token']);

            $this->logAPIActivity('logout_success', [
                'administrator_id' => $admin['id']
            ]);

            $this->apiSuccess([], 'Successfully logged out');

        } catch (\Exception $e) {
            $this->apiError('Logout failed', 500);
        }
    }

    /**
     * Revoke all tokens for current admin
     * POST /api/auth/logout-all
     */
    public function logoutAll() {
        $this->validateAPIMethod(['POST']);

        $admin = $this->authenticateAPI(true);

        try {
            // Revoke all tokens for this admin
            $this->revokeAllTokensForAdmin($admin['id']);

            $this->logAPIActivity('logout_all_success', [
                'administrator_id' => $admin['id']
            ]);

            $this->apiSuccess([], 'All sessions logged out successfully');

        } catch (\Exception $e) {
            $this->apiError('Logout all failed', 500);
        }
    }

    /**
     * Get current user info
     * GET /api/auth/me
     */
    public function me() {
        $this->validateAPIMethod(['GET']);

        $admin = $this->authenticateAPI(true);

        $this->apiSuccess([
            'admin' => [
                'id' => $admin['id'],
                'username' => $admin['username'],
                'email' => $admin['email']
            ],
            'token_info' => [
                'expires_at' => $admin['token_expires'],
                'last_used_at' => $admin['last_used_at']
            ]
        ]);
    }

    /**
     * Generate cryptographically secure token
     */
    private function generateSecureToken($length = 64) {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Store API token in database with metadata
     */
    private function storeAPIToken($adminId, $token, $expiresAt, $userAgent = null, $ipAddress = null) {
        // Optional: Limit number of active tokens per admin (e.g., max 5)
        $this->cleanupExpiredTokens($adminId);
        $this->limitActiveTokens($adminId, 5);

        // Insert new token
        $stmt = $this->db->prepare("
            INSERT INTO api_tokens (
                administrator_id, 
                token_hash, 
                expires_at, 
                created_at, 
                last_used_at,
                user_agent,
                ip_address
            ) VALUES (?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, ?, ?)
        ");

        $stmt->execute([
            $adminId,
            hash('sha256', $token),
            $expiresAt,
            $userAgent,
            $ipAddress
        ]);
    }

    /**
     * Revoke specific API token
     */
    private function revokeAPIToken($token) {
        $stmt = $this->db->prepare("
            UPDATE api_tokens 
            SET revoked_at = CURRENT_TIMESTAMP 
            WHERE token_hash = ? AND revoked_at IS NULL
        ");
        $stmt->execute([hash('sha256', $token)]);
    }

    /**
     * Revoke all tokens for an admin
     */
    private function revokeAllTokensForAdmin($adminId) {
        $stmt = $this->db->prepare("
            UPDATE api_tokens 
            SET revoked_at = NOW() 
            WHERE administrator_id = ? AND revoked_at IS NULL
        ");
        $stmt->execute([$adminId]);
    }

    /**
     * Clean up expired tokens
     */
    private function cleanupExpiredTokens($adminId) {
        $stmt = $this->db->prepare("
            DELETE FROM api_tokens 
            WHERE administrator_id = ? AND expires_at < CURRENT_TIMESTAMP
        ");
        $stmt->execute([$adminId]);
    }

    /**
     * Limit active tokens per admin
     */
    private function limitActiveTokens($adminId, $maxTokens = 5) {
        // Only keep the most recent $maxTokens tokens
        $stmt = $this->db->prepare("
            SELECT id FROM api_tokens 
            WHERE administrator_id = ? AND revoked_at IS NULL AND expires_at > CURRENT_TIMESTAMP
            ORDER BY created_at DESC
            LIMIT -1 OFFSET ?
        ");
        $stmt->execute([$adminId, $maxTokens]);
        $tokensToRevoke = $stmt->fetchAll();
        foreach ($tokensToRevoke as $token) {
            $stmt2 = $this->db->prepare("UPDATE api_tokens SET revoked_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt2->execute([$token['id']]);
        }
    }
}
