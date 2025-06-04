<?php
namespace App\Controllers;

use Core\BaseController;
use App\Models\Administrator;

class AuthController extends BaseController {
    public function __construct() {
        parent::__construct();
        // Redirect to dashboard if already logged in
        if (isset($_SESSION['admin_id']) && $this->route !== '/logout') {
            $this->redirect('/dashboard');
        }
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            $admin = new Administrator();
            $result = $admin->authenticate($username, $password);

            if ($result) {
                $_SESSION['admin_id'] = $result['id'];
                $_SESSION['admin_username'] = $result['username'];
                $this->redirect('/dashboard');
            } else {
                $this->view('auth/login', [
                    'error' => 'Invalid username or password'
                ]);
            }
        } else {
            $this->view('auth/login');
        }
    }

    public function logout() {
        session_destroy();
        $this->redirect('/login');
    }
} 