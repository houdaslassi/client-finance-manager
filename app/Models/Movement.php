<?php
namespace App\Models;

use Core\BaseModel;

class Movement extends BaseModel {
    protected $table = 'movements';

    public function create($data) {
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
        $sql = "SELECT SUM(amount) as total FROM movements WHERE type = 'income'";
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
} 