<?php
namespace App\Models;

use Core\BaseModel;

class Client extends BaseModel {
    protected $table = 'clients';

    public function __construct() {
        parent::__construct();
    }

    public function validate($data, $id = null) {
        $errors = [];

        if (empty($data['name'])) {
            $errors['name'] = 'Name is required';
        }

        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        } else {
            // Check for duplicate email
            $sql = "SELECT id FROM clients WHERE email = ?";
            $params = [$data['email']];
            if ($id) {
                $sql .= " AND id != ?";
                $params[] = $id;
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            if ($stmt->fetch()) {
                $errors['email'] = 'Email already exists';
            }
        }

        if (empty($data['phone'])) {
            $errors['phone'] = 'Phone number is required';
        }

        return $errors;
    }

    public function create($data) {
        $errors = $this->validate($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        try {
            return parent::create($data);
        } catch (\PDOException $e) {
            if (strpos($e->getMessage(), 'UNIQUE') !== false) {
                return ['success' => false, 'errors' => ['email' => 'Email already exists']];
            }
            throw $e;
        }
    }

    public function update($id, $data) {
        $errors = $this->validate($data, $id);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        return parent::update($id, $data);
    }

    public function getAllWithBalance() {
        $sql = "SELECT c.*, 
                COALESCE(SUM(CASE WHEN m.type IN ('income', 'earning') THEN m.amount WHEN m.type = 'expense' THEN -m.amount ELSE 0 END), 0) as balance 
                FROM {$this->table} c 
                LEFT JOIN movements m ON c.id = m.client_id 
                GROUP BY c.id 
                ORDER BY c.name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getWithMovements($id) {
        // Get client details
        $client = $this->find($id);
        if (!$client) {
            return null;
        }

        // Get client's movements
        $sql = "SELECT * FROM movements WHERE client_id = ? ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $movements = $stmt->fetchAll();

        // Calculate balance
        $balance = 0;
        foreach ($movements as $movement) {
            $type = strtolower($movement['type']);
            if ($type === 'income' || $type === 'earning') {
                $balance += $movement['amount'];
            } elseif ($type === 'expense') {
                $balance -= $movement['amount'];
            }
        }

        return [
            'client' => $client,
            'movements' => $movements,
            'balance' => $balance
        ];
    }

    public function getBalance() {
        $stmt = $this->db->prepare("
            SELECT 
                COALESCE(SUM(CASE WHEN type = 'earning' THEN amount ELSE -amount END), 0) as balance
            FROM movements 
            WHERE client_id = ?
        ");
        $stmt->execute([$this->id]);
        return $stmt->fetch()['balance'];
    }

    public function getMovements($startDate = null, $endDate = null) {
        $sql = "SELECT * FROM movements WHERE client_id = ?";
        $params = [$this->id];

        if ($startDate) {
            $sql .= " AND date >= ?";
            $params[] = $startDate;
        }
        if ($endDate) {
            $sql .= " AND date <= ?";
            $params[] = $endDate;
        }

        $sql .= " ORDER BY date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function all()
    {
        $sql = "SELECT * FROM clients ORDER BY name ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function count() {
        $sql = "SELECT COUNT(*) as total FROM clients";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function getPaginated($page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTotalCount() {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM {$this->table}");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
} 