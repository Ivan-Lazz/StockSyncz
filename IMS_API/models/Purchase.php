<?php
// models/Purchase.php
class Purchase {
    private $conn;
    private $purchase_table = "purchase_master";
    private $stock_table = "stock_master";

    public $id;
    public $company_name;
    public $product_name;
    public $unit;
    public $packing_size;
    public $qty;
    public $price;
    public $party_name;
    public $purchase_type;
    public $expiry_date;
    public $purchase_date;
    public $purchased_by;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        try {
            // Start transaction
            $this->conn->beginTransaction();
            
            error_log("Starting purchase creation for product: " . $this->product_name);
    
            // Validate numeric values
            if (!is_numeric($this->qty) || $this->qty <= 0) {
                throw new Exception("Invalid quantity value");
            }
            if (!is_numeric($this->price) || $this->price <= 0) {
                throw new Exception("Invalid price value");
            }
    
            // Insert into purchase_master with exact column names from the database
            $query = "INSERT INTO purchase_master 
                    (company_name, product_name, unit, packing_size, 
                    quantity, price, party_name, purchase_type, expiry_date, 
                    purchase_date, username) 
                    VALUES 
                    (:company_name, :product_name, :unit, :packing_size, 
                    :quantity, :price, :party_name, :purchase_type, :expiry_date, 
                    :purchase_date, :username)";
    
            $stmt = $this->conn->prepare($query);
    
            // Bind values with correct column names
            $stmt->bindParam(":company_name", $this->company_name);
            $stmt->bindParam(":product_name", $this->product_name);
            $stmt->bindParam(":unit", $this->unit);
            $stmt->bindParam(":packing_size", $this->packing_size);
            $stmt->bindParam(":quantity", $this->qty);
            $stmt->bindParam(":price", $this->price);
            $stmt->bindParam(":party_name", $this->party_name);
            $stmt->bindParam(":purchase_type", $this->purchase_type);
            $stmt->bindParam(":expiry_date", $this->expiry_date);
            $stmt->bindParam(":purchase_date", $this->purchase_date);
            $stmt->bindParam(":username", $this->purchased_by); // Changed to username
    
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert purchase record: " . implode(", ", $stmt->errorInfo()));
            }
    
            // Check existing stock
            $check_query = "SELECT product_qty FROM stock_master 
                        WHERE product_company = :company_name 
                        AND product_name = :product_name 
                        AND product_unit = :unit";
    
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(":company_name", $this->company_name);
            $check_stmt->bindParam(":product_name", $this->product_name);
            $check_stmt->bindParam(":unit", $this->unit);
            $check_stmt->execute();
    
            if ($check_stmt->rowCount() > 0) {
                // Update existing stock
                $update_query = "UPDATE stock_master 
                            SET product_qty = product_qty + :quantity 
                            WHERE product_company = :company_name 
                            AND product_name = :product_name 
                            AND product_unit = :unit";
    
                $update_stmt = $this->conn->prepare($update_query);
                $update_stmt->bindParam(":quantity", $this->qty);
                $update_stmt->bindParam(":company_name", $this->company_name);
                $update_stmt->bindParam(":product_name", $this->product_name);
                $update_stmt->bindParam(":unit", $this->unit);
    
                if (!$update_stmt->execute()) {
                    throw new Exception("Failed to update stock: " . implode(", ", $update_stmt->errorInfo()));
                }
            } else {
                // Insert new stock record
                $insert_query = "INSERT INTO stock_master 
                            (product_company, product_name, product_unit, 
                                packing_size, product_qty, product_selling_price) 
                            VALUES 
                            (:company_name, :product_name, :unit, 
                                :packing_size, :quantity, :price)";
    
                $insert_stmt = $this->conn->prepare($insert_query);
                $insert_stmt->bindParam(":company_name", $this->company_name);
                $insert_stmt->bindParam(":product_name", $this->product_name);
                $insert_stmt->bindParam(":unit", $this->unit);
                $insert_stmt->bindParam(":packing_size", $this->packing_size);
                $insert_stmt->bindParam(":quantity", $this->qty);
                $insert_stmt->bindParam(":price", $this->price);
    
                if (!$insert_stmt->execute()) {
                    throw new Exception("Failed to insert stock: " . implode(", ", $insert_stmt->errorInfo()));
                }
            }
    
            // Commit transaction
            $this->conn->commit();
            error_log("Purchase creation completed successfully");
            return true;
    
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollBack();
            error_log("Purchase creation failed: " . $e->getMessage());
            throw $e;
        }
    }

    // Get products by company
    public function getProductsByCompany($company_name) {
        $query = "SELECT DISTINCT product_name FROM products 
                WHERE company_name = :company_name 
                ORDER BY product_name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":company_name", $company_name);
        $stmt->execute();
        return $stmt;
    }

    // Get units by product and company
    public function getUnitsByProduct($product_name, $company_name) {
        $query = "SELECT DISTINCT unit FROM products 
                WHERE company_name = :company_name 
                AND product_name = :product_name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":company_name", $company_name);
        $stmt->bindParam(":product_name", $product_name);
        $stmt->execute();
        return $stmt;
    }

    // Get packing sizes
    public function getPackingSizes($unit, $product_name, $company_name) {
        $query = "SELECT DISTINCT packing_size FROM products 
                WHERE company_name = :company_name 
                AND product_name = :product_name 
                AND unit = :unit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":company_name", $company_name);
        $stmt->bindParam(":product_name", $product_name);
        $stmt->bindParam(":unit", $unit);
        $stmt->execute();
        return $stmt;
    }

    public function getParties() {
        try {
            $query = "SELECT businessname FROM party_info ORDER BY businessname";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch(PDOException $e) {
            error_log("Error getting parties: " . $e->getMessage());
            throw $e;
        }
    }
}