<?php

class Party {
    private $conn;
    private $table_name = "party_info";

    public $id;
    public $firstname;
    public $lastname;
    public $businessname;
    public $contact;
    public $address;
    public $city;

    public function __construct($db) {
        $this->conn = $db;
    }

    private function validateInput() {
        if (empty($this->firstname) || empty($this->lastname) || 
            empty($this->businessname) || empty($this->contact) ||
            empty($this->city)) {
            return false;
        }
        
        // Sanitize input
        $this->firstname = htmlspecialchars(strip_tags($this->firstname));
        $this->lastname = htmlspecialchars(strip_tags($this->lastname));
        $this->businessname = htmlspecialchars(strip_tags($this->businessname));
        $this->contact = htmlspecialchars(strip_tags($this->contact));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->city = htmlspecialchars(strip_tags($this->city));
        
        return true;
    }

    public function create() {
        if (!$this->validateInput()) {
            return false;
        }

        if ($this->businessExists()) {
            return false;
        }

        $query = "INSERT INTO " . $this->table_name . "
                (firstname, lastname, businessname, contact, address, city)
                VALUES
                (:firstname, :lastname, :businessname, :contact, :address, :city)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":firstname", $this->firstname);
        $stmt->bindParam(":lastname", $this->lastname);
        $stmt->bindParam(":businessname", $this->businessname);
        $stmt->bindParam(":contact", $this->contact);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":city", $this->city);

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

        $query = "UPDATE " . $this->table_name . "
                SET firstname = :firstname,
                    lastname = :lastname,
                    businessname = :businessname,
                    contact = :contact,
                    address = :address,
                    city = :city
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":firstname", $this->firstname);
        $stmt->bindParam(":lastname", $this->lastname);
        $stmt->bindParam(":businessname", $this->businessname);
        $stmt->bindParam(":contact", $this->contact);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":city", $this->city);
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

    private function businessExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE businessname = :businessname";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":businessname", $this->businessname);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}