<?php
namespace App\Controllers;

use Core\BaseController;
use App\Models\Administrator;

class AuthController extends BaseController {
    public function __construct() {
        parent::__construct();
        error_log("AuthController constructed");

        // Get current route properly
        $currentRoute = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Redirect to dashboard if already logged in (except for logout)
        if (isset($_SESSION['admin_id']) && $currentRoute !== '/logout') {
            $this->redirect('/dashboard');
        }
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Protection
            /*if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $this->render('auth/login', ['error' => 'Invalid request']);
                return;
            }*/

            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']);

            $admin = new Administrator();
            $result = $admin->authenticate($username, $password);

            if ($result) {
                $_SESSION['admin_id'] = $result['id'];
                $_SESSION['admin_username'] = $result['username'];
                $_SESSION['last_activity'] = time();

                // Remember me functionality
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    setcookie('remember_token', $token, time() + (86400 * 30), '/'); // 30 days
                    $admin->saveRememberToken($result['id'], $token);
                }

                $this->redirect('/dashboard');
            } else {
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
        error_log("Logout method called");

        // Clear all session variables
        $_SESSION = array();
        error_log("Session variables cleared");

        // Destroy the session
        session_destroy();
        error_log("Session destroyed");

        // Clear cookies
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
            error_log("Session cookie cleared");
        }
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
            error_log("Remember token cookie cleared");
        }

        // Force redirect
        error_log("Redirecting to login page");
        header('Location: /login');
        exit();
    }

    private function generateCsrfToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}
