<?php
namespace Core;

class BaseController {
    protected $db;
    protected $session;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->session = $_SESSION;
    }

    protected function render($view, $data = []) {
        extract($data);
        require_once dirname(__DIR__) . "/Views/{$view}.php";
    }

    protected function redirect($url, $message = null) {
        error_log("Redirecting to: " . $url);
        if ($message) {
            error_log("With message: " . $message);
            $_SESSION['flash_message'] = $message;
        }
        header("Location: {$url}");
        exit;
    }

    protected function json($data, $status = 200) {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit;
    }

    protected function isLoggedIn() {
        return isset($this->session['user_id']);
    }

    protected function requireAdmin() {
        if (!$this->isLoggedIn() || $this->session['role'] !== 'admin') {
            $this->unauthorized();
        }
    }

    protected function notFound() {
        header("HTTP/1.0 404 Not Found");
        echo "404 Not Found";
        exit;
    }

    protected function unauthorized() {
        header("HTTP/1.0 401 Unauthorized");
        echo "401 Unauthorized";
        exit;
    }

    protected function requireAuth() {
        if (!isset($_SESSION['admin_id'])) {
            header('Location: /login');
            exit();
        }
    }

    protected function view($view, $data = [])
    {
        extract($data);
        require __DIR__ . '/../Views/' . $view . '.php';
    }
}
