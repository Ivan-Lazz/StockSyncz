<?php

class Company {
    private $conn;
    private $table_name = "company_name";

    public $id;
    public $companyname;

    public function __construct($db) {
        $this->conn = $db;
    }

    private function validateInput() {
        if (empty($this->companyname)) {
            return false;
        }
        
        $this->companyname = htmlspecialchars(strip_tags($this->companyname));
        return true;
    }

    public function create() {
        if (!$this->validateInput()) {
            return false;
        }

        if ($this->companyExists()) {
            return false;
        }

        $query = "INSERT INTO " . $this->table_name . " VALUES (NULL, :companyname)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":companyname", $this->companyname);

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

        if ($this->companyExistsExceptCurrent()) {
            return false;
        }

        $query = "UPDATE " . $this->table_name . " SET companyname = :companyname WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":companyname", $this->companyname);
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

    private function companyExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE companyname = :companyname";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":companyname", $this->companyname);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    private function companyExistsExceptCurrent() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE companyname = :companyname AND id != :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":companyname", $this->companyname);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}