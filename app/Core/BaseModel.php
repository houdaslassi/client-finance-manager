<?php
namespace Core;

class BaseModel {
    protected $db;
    protected $table;

    public function __construct() {
        // Use the Database singleton instead of creating a new connection
        $this->db = Database::getInstance()->getConnection();
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findAll() {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table}");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function create($data) {
        $fields = array_keys($data);
        $values = array_fill(0, count($fields), '?');

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") 
                VALUES (" . implode(', ', $values) . ")";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));

        return ['success' => true, 'id' => $this->db->lastInsertId()];
    }

    public function update($id, $data) {
        $fields = array_map(function($field) {
            return "$field = ?";
        }, array_keys($data));

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";

        $values = array_values($data);
        $values[] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function count() {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table}");
        $stmt->execute();
        return $stmt->fetchColumn();
    }
}
