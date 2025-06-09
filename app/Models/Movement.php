<?php
namespace App\Models;

use Core\BaseModel;

class Movement extends BaseModel {
    protected $table = 'movements';

    public function validate($data)
    {
        $errors = [];

        if (empty($data['client_id'])) {
            $errors['client_id'] = 'Client ID is required';
        }

        if (empty($data['type'])) {
            $errors['type'] = 'Type is required';
        } elseif (!in_array($data['type'], ['income', 'earning', 'expense'])) {
            $errors['type'] = 'Invalid type. Must be income, earning, or expense';
        }

        if (empty($data['amount'])) {
            $errors['amount'] = 'Amount is required';
        } elseif (!is_numeric($data['amount']) || $data['amount'] <= 0) {
            $errors['amount'] = 'Amount must be a positive number';
        }

        return $errors;
    }

    public function create($data) {
        $errors = $this->validate($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        $data['date'] = date('Y-m-d H:i:s');
        return parent::create($data);
    }

    public function getClientMovements($clientId, $startDate = null, $endDate = null) {
        $sql = "SELECT * FROM movements WHERE client_id = ?";
        $params = [$clientId];

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

    public function getTotalBalance($clientId) {
        $stmt = $this->db->prepare("
            SELECT 
                COALESCE(SUM(CASE WHEN type = 'earning' THEN amount ELSE -amount END), 0) as balance
            FROM movements 
            WHERE client_id = ?
        ");
        $stmt->execute([$clientId]);
        return $stmt->fetch()['balance'];
    }

    public function allWithClients($startDate = null, $endDate = null)
    {
        $sql = "SELECT m.*, c.name as client_name FROM movements m JOIN clients c ON m.client_id = c.id";
        $params = [];
        $conditions = [];
        if ($startDate) {
            $conditions[] = "m.date >= ?";
            $params[] = $startDate;
        }
        if ($endDate) {
            $conditions[] = "m.date <= ?";
            $params[] = $endDate;
        }
        if ($conditions) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        $sql .= " ORDER BY m.date DESC, m.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function count() {
        $sql = "SELECT COUNT(*) as total FROM movements";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function getTotalIncome() {
        $sql = "SELECT SUM(amount) as total FROM movements WHERE type IN ('income', 'earning')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function getTotalExpenses() {
        $sql = "SELECT SUM(amount) as total FROM movements WHERE type = 'expense'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function getByClientId($clientId) {
        $stmt = $this->db->prepare("
            SELECT m.*, c.name as client_name, c.email as client_email 
            FROM {$this->table} m 
            LEFT JOIN clients c ON m.client_id = c.id 
            WHERE m.client_id = ? 
            ORDER BY m.created_at DESC
        ");
        $stmt->execute([$clientId]);
        return $stmt->fetchAll();
    }

    public function getPaginated($page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        $stmt = $this->db->prepare("SELECT m.*, c.name as client_name FROM movements m JOIN clients c ON m.client_id = c.id ORDER BY m.date DESC, m.id DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTotalCount() {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM movements");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
} 