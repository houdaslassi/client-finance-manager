<?php
namespace App\Controllers;

use Core\BaseController;
use App\Models\Administrator;

class AuthController extends BaseController {
    public function __construct() {
        parent::__construct();

        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $currentRoute = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Redirect to dashboard if already logged in (except for logout)
        if (isset($_SESSION['admin_id']) && $currentRoute !== '/logout') {
            $this->redirect('/dashboard');
        }
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Protection - FIXED VERSION
            if (!$this->validateCsrfToken()) {
                $this->render('auth/login', [
                    'error' => 'Invalid request. Please try again.',
                    'csrf_token' => $this->generateCsrfToken()
                ]);
                return;
            }

            // Rate limiting (basic protection)
            if ($this->isRateLimited()) {
                $this->render('auth/login', [
                    'error' => 'Too many login attempts. Please wait before trying again.',
                    'csrf_token' => $this->generateCsrfToken()
                ]);
                return;
            }

            // Input sanitization and validation
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']);

            // Basic input validation
            if (empty($username) || empty($password)) {
                $this->render('auth/login', [
                    'error' => 'Username and password are required',
                    'csrf_token' => $this->generateCsrfToken()
                ]);
                return;
            }

            // Length validation to prevent extremely long inputs
            if (strlen($username) > 255 || strlen($password) > 1000) {
                $this->render('auth/login', [
                    'error' => 'Invalid input length',
                    'csrf_token' => $this->generateCsrfToken()
                ]);
                return;
            }

            $admin = new Administrator();
            $result = $admin->authenticate($username, $password);

            if ($result) {
                // Regenerate session ID for security
                session_regenerate_id(true);

                $_SESSION['admin_id'] = $result['id'];
                $_SESSION['admin_username'] = $result['username'];
                $_SESSION['last_activity'] = time();
                $_SESSION['login_time'] = time();
                $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
                $_SESSION['ip_address'] = $this->getRealIpAddress();

                // Clear any failed login attempts
                $this->clearFailedAttempts();

                // Remember me functionality (with secure token)
                if ($remember) {
                    $this->setRememberToken($result['id'], $admin);
                }

                // Log successful login
                error_log("Successful login for user: {$username} from IP: " . $this->getRealIpAddress());

                $this->redirect('/dashboard');
            } else {
                // Log failed attempt
                $this->logFailedAttempt();
                error_log("Failed login attempt for user: {$username} from IP: " . $this->getRealIpAddress());

                // Add delay to prevent brute force
                sleep(1);

                $this->render('auth/login', [
                    'error' => 'Invalid username or password',
                    'csrf_token' => $this->generateCsrfToken()
                ]);
            }
        } else {
            $this->render('auth/login', [
                'csrf_token' => $this->generateCsrfToken()
            ]);
        }
    }

    public function logout() {
        error_log("Logout method called for user: " . ($_SESSION['admin_username'] ?? 'unknown'));

        // Clear remember token if exists
        if (isset($_COOKIE['remember_token'])) {
            $admin = new Administrator();
            $admin->clearRememberToken($_SESSION['admin_id'] ?? null);
        }

        // Clear all session variables
        $_SESSION = array();

        // Destroy the session
        session_destroy();

        // Clear cookies securely
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/', '', true, true);
        }
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/', '', true, true);
        }

        header('Location: /login');
        exit();
    }

    /**
     * Generate CSRF token
     */
    private function generateCsrfToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate CSRF token - FIXED VERSION
     */
    private function validateCsrfToken() {
        $sessionToken = $_SESSION['csrf_token'] ?? null;
        $postToken = $_POST['csrf_token'] ?? null;

        // Debug CSRF tokens (remove in production)
        error_log("Session CSRF token: " . ($sessionToken ?? 'null'));
        error_log("POST CSRF token: " . ($postToken ?? 'null'));

        if (!$sessionToken || !$postToken) {
            error_log("CSRF validation failed: missing tokens");
            return false;
        }

        // Use hash_equals to prevent timing attacks
        if (!hash_equals($sessionToken, $postToken)) {
            error_log("CSRF validation failed: tokens don't match");
            return false;
        }

        return true;
    }

    /**
     * Basic rate limiting
     */
    private function isRateLimited() {
        $ip = $this->getRealIpAddress();
        $key = 'login_attempts_' . $ip;

        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 0, 'last_attempt' => 0];
        }

        $attempts = $_SESSION[$key];
        $now = time();

        // Reset counter if 15 minutes have passed
        if ($now - $attempts['last_attempt'] > 900) {
            $_SESSION[$key] = ['count' => 0, 'last_attempt' => $now];
            return false;
        }

        // Allow max 5 attempts per 15 minutes
        return $attempts['count'] >= 5;
    }

    /**
     * Log failed login attempt
     */
    private function logFailedAttempt() {
        $ip = $this->getRealIpAddress();
        $key = 'login_attempts_' . $ip;

        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 0, 'last_attempt' => 0];
        }

        $_SESSION[$key]['count']++;
        $_SESSION[$key]['last_attempt'] = time();
    }

    /**
     * Clear failed login attempts
     */
    private function clearFailedAttempts() {
        $ip = $this->getRealIpAddress();
        $key = 'login_attempts_' . $ip;
        unset($_SESSION[$key]);
    }

    /**
     * Set secure remember token
     */
    private function setRememberToken($adminId, $admin) {
        $token = bin2hex(random_bytes(64)); // Longer token for better security

        // Set secure cookie
        setcookie(
            'remember_token',
            $token,
            time() + (86400 * 30), // 30 days
            '/',
            '',
            isset($_SERVER['HTTPS']), // Secure flag if HTTPS
            true // HttpOnly flag
        );

        $admin->saveRememberToken($adminId, hash('sha256', $token)); // Store hashed token
    }

    /**
     * Get real IP address (handles proxies)
     */
    private function getRealIpAddress() {
        $headers = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                // Handle comma-separated IPs (from proxies)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                // Validate IP format
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
}
