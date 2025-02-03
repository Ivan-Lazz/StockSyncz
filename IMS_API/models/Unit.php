<?php

class Unit {
    private $conn;
    private $table_name = "units";

    public $id;
    public $unit;

    public function __construct($db) {
        $this->conn = $db;
    }

    private function validateInput() {
        if (empty($this->unit)) {
            return false;
        }
        
        $this->unit = htmlspecialchars(strip_tags($this->unit));
        return true;
    }

    public function create() {
        if (!$this->validateInput()) {
            return false;
        }

        // Check if unit exists
        if ($this->unitExists()) {
            return false;
        }

        $query = "INSERT INTO " . $this->table_name . " VALUES (NULL, :unit)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":unit", $this->unit);

        try {
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }

    public function update() {
        if (!$this->validateInput()) {
            return false;
        }

        // Check if unit exists (excluding current ID)
        if ($this->unitExistsExceptCurrent()) {
            return false;
        }

        $query = "UPDATE " . $this->table_name . " SET unit = :unit WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":unit", $this->unit);
        $stmt->bindParam(":id", $this->id);

        try {
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);

        try {
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readSingle($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt;
    }

    private function unitExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE unit = :unit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":unit", $this->unit);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    private function unitExistsExceptCurrent() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE unit = :unit AND id != :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":unit", $this->unit);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}