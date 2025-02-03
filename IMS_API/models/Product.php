<?php

class Product {
    private $conn;
    private $table_name = "products";

    public $id;
    public $company_name;
    public $product_name;
    public $unit;
    public $packing_size;

    public function __construct($db) {
        $this->conn = $db;
    }

    private function validateInput() {
        if (empty($this->company_name) || empty($this->product_name) || 
            empty($this->unit) || empty($this->packing_size)) {
            return false;
        }
        
        $this->company_name = htmlspecialchars(strip_tags($this->company_name));
        $this->product_name = htmlspecialchars(strip_tags($this->product_name));
        $this->unit = htmlspecialchars(strip_tags($this->unit));
        $this->packing_size = htmlspecialchars(strip_tags($this->packing_size));
        
        return true;
    }

    public function create() {
        if (!$this->validateInput()) {
            return false;
        }

        if ($this->productExists()) {
            return false;
        }

        $query = "INSERT INTO " . $this->table_name . "
                (company_name, product_name, unit, packing_size)
                VALUES
                (:company_name, :product_name, :unit, :packing_size)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":company_name", $this->company_name);
        $stmt->bindParam(":product_name", $this->product_name);
        $stmt->bindParam(":unit", $this->unit);
        $stmt->bindParam(":packing_size", $this->packing_size);

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

        if ($this->productExistsExceptCurrent()) {
            return false;
        }

        $query = "UPDATE " . $this->table_name . "
                SET company_name = :company_name,
                    product_name = :product_name,
                    unit = :unit,
                    packing_size = :packing_size
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":company_name", $this->company_name);
        $stmt->bindParam(":product_name", $this->product_name);
        $stmt->bindParam(":unit", $this->unit);
        $stmt->bindParam(":packing_size", $this->packing_size);
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

    private function productExists() {
        $query = "SELECT id FROM " . $this->table_name . " 
                WHERE company_name = :company_name 
                AND product_name = :product_name 
                AND unit = :unit 
                AND packing_size = :packing_size";

        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":company_name", $this->company_name);
        $stmt->bindParam(":product_name", $this->product_name);
        $stmt->bindParam(":unit", $this->unit);
        $stmt->bindParam(":packing_size", $this->packing_size);
        
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    private function productExistsExceptCurrent() {
        $query = "SELECT id FROM " . $this->table_name . " 
                WHERE company_name = :company_name 
                AND product_name = :product_name 
                AND unit = :unit 
                AND packing_size = :packing_size 
                AND id != :id";

        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":company_name", $this->company_name);
        $stmt->bindParam(":product_name", $this->product_name);
        $stmt->bindParam(":unit", $this->unit);
        $stmt->bindParam(":packing_size", $this->packing_size);
        $stmt->bindParam(":id", $this->id);
        
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Get all companies for dropdown
    public function getCompanies() {
        $query = "SELECT * FROM company_name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Get all units for dropdown
    public function getUnits() {
        $query = "SELECT * FROM units";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}