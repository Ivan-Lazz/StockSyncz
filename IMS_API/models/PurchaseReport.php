<?php
// models/PurchaseReport.php
class PurchaseReport {
    private $conn;
    private $table_name = "purchase_master";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getPurchases($start_date = null, $end_date = null) {
        try {
            if ($start_date && $end_date) {
                $query = "SELECT * FROM " . $this->table_name . " 
                        WHERE purchase_date >= :start_date 
                        AND purchase_date <= :end_date 
                        ORDER BY purchase_date DESC";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":start_date", $start_date);
                $stmt->bindParam(":end_date", $end_date);
            } else {
                $query = "SELECT * FROM " . $this->table_name . " 
                        ORDER BY purchase_date DESC";
                
                $stmt = $this->conn->prepare($query);
            }

            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Error fetching purchase records: " . $e->getMessage());
        }
    }
}