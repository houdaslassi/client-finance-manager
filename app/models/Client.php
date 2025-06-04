<?php
namespace App\Models;

use Core\BaseModel;

class Client extends BaseModel {
    protected $table = 'clients';

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
} 