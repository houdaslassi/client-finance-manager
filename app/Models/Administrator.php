<?php
namespace App\Models;

use Core\BaseModel;

class Administrator extends BaseModel {
    protected $table = 'administrators';

    public function __construct() {
        parent::__construct();
    }

    public function authenticate($username, $password) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            return $admin;
        }
        return false;
    }

    public function create($data) {
        if (!isset($data['password'])) {
            return false;
        }

        // Validate password strength
        if (!$this->validatePassword($data['password'])) {
            return ['success' => false, 'error' => 'Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character.'];
        }

        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        return parent::create($data);
    }

    public function findByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    private function validatePassword($password) {
        // Password must be at least 8 characters long and contain at least one uppercase letter,
        // one lowercase letter, one number, and one special character
        return strlen($password) >= 8 &&
               preg_match('/[A-Z]/', $password) &&
               preg_match('/[a-z]/', $password) &&
               preg_match('/[0-9]/', $password) &&
               preg_match('/[^A-Za-z0-9]/', $password);
    }
} 