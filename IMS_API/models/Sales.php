<?php

class Sales {
    private $conn;
    private $billing_header = "billing_header";
    private $billing_details = "billing_details";
    private $stock_table = "stock_master";

    public $id;
    public $full_name;
    public $bill_type;
    public $bill_date;
    public $bill_no;
    public $username;
    
    // For details
    public $company_name;
    public $product_name;
    public $unit;
    public $packing_size;
    public $price;
    public $quantity;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Add item to cart session
    public function addToCart($data) {
        try {
            $availableQty = $this->checkQuantityAvailable($data);
            
            if ($availableQty < $data['qty']) {
                throw new Exception("Only {$availableQty} units available in stock");
            }

            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = array(array(
                    "company_name" => $data['company_name'],
                    "product_name" => $data['product_name'],
                    "unit" => $data['unit'],
                    "packing_size" => $data['packing_size'],
                    "price" => $data['price'],
                    "qty" => $data['qty']
                ));
                return true;
            }

            $duplicate = $this->checkDuplicateProduct($data);
            if ($duplicate === 0) {
                array_push($_SESSION['cart'], array(
                    "company_name" => $data['company_name'],
                    "product_name" => $data['product_name'],
                    "unit" => $data['unit'],
                    "packing_size" => $data['packing_size'],
                    "price" => $data['price'],
                    "qty" => $data['qty']
                ));
                return true;
            } else {
                $existingQty = $this->getExistingQuantity($data);
                $newQty = $existingQty + $data['qty'];
                
                if ($availableQty < $newQty) {
                    throw new Exception("Cannot add quantity. Only {$availableQty} units available in stock");
                }
                
                $sessionIndex = $this->getProductSessionIndex($data);
                $_SESSION['cart'][$sessionIndex]['qty'] = $newQty;
                return true;
            }

        } catch (Exception $e) {
            error_log("Error adding to cart: " . $e->getMessage());
            throw $e;
        }
    }

    // Create billing entry
    public function createBill($header_data, $cart_data) {
        try {
            $this->conn->beginTransaction();
    
            // Debug logging
            error_log("Creating bill with header: " . print_r($header_data, true));
            error_log("Cart data: " . print_r($cart_data, true));
    
            // Insert billing header
            $header_query = "INSERT INTO billing_header 
                            (full_name, bill_type, date, bill_no, username)
                            VALUES 
                            (:full_name, :bill_type, :date, :bill_no, :username)";
    
            $stmt = $this->conn->prepare($header_query);
            
            $date = date('Y-m-d');

            $stmt->bindParam(":full_name", $header_data['full_name']);
            $stmt->bindParam(":bill_type", $header_data['bill_type']);
            $stmt->bindParam(":date", $date); // Use current date
            $stmt->bindParam(":bill_no", $header_data['bill_no']);
            $stmt->bindParam(":username", $header_data['username']);
    
            if (!$stmt->execute()) {
                throw new Exception("Failed to create billing header: " . implode(", ", $stmt->errorInfo()));
            }
    
            $bill_id = $this->conn->lastInsertId();
            error_log("Created bill header with ID: " . $bill_id);
    
            // Insert billing details
            foreach ($cart_data as $item) {
                if (empty($item['company_name'])) continue; // Skip empty items
    
                $detail_query = "INSERT INTO billing_details 
                            (bill_id, product_company, product_name, product_unit,
                                packaging_size, price, qty)
                            VALUES 
                            (:bill_id, :product_company, :product_name, :product_unit,
                                :packaging_size, :price, :qty)";
    
                $stmt = $this->conn->prepare($detail_query);
                
                $stmt->bindParam(":bill_id", $bill_id);
                $stmt->bindParam(":product_company", $item['company_name']);
                $stmt->bindParam(":product_name", $item['product_name']);
                $stmt->bindParam(":product_unit", $item['unit']);
                $stmt->bindParam(":packaging_size", $item['packing_size']);
                $stmt->bindParam(":price", $item['price']);
                $stmt->bindParam(":qty", $item['qty']);
    
                if (!$stmt->execute()) {
                    throw new Exception("Failed to insert bill detail: " . implode(", ", $stmt->errorInfo()));
                }
    
                error_log("Inserted bill detail for product: " . $item['product_name']);
    
                // Update stock
                $stock_query = "UPDATE stock_master 
                            SET product_qty = product_qty - :qty
                            WHERE product_company = :company_name 
                            AND product_name = :product_name 
                            AND product_unit = :unit";
    
                $stmt = $this->conn->prepare($stock_query);
                
                $stmt->bindParam(":qty", $item['qty']);
                $stmt->bindParam(":company_name", $item['company_name']);
                $stmt->bindParam(":product_name", $item['product_name']);
                $stmt->bindParam(":unit", $item['unit']);
    
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update stock: " . implode(", ", $stmt->errorInfo()));
                }
            }
    
            $this->conn->commit();
            error_log("Bill creation completed successfully");
            return $bill_id;
    
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error in createBill: " . $e->getMessage());
            throw $e;
        }
    }

    // Helper functions for cart management
    public function checkQuantityAvailable($data) {
        try {
            $query = "SELECT product_qty FROM " . $this->stock_table . "
                    WHERE product_company = :company_name 
                    AND product_name = :product_name 
                    AND product_unit = :unit 
                    AND packing_size = :packing_size";

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(":company_name", $data['company_name']);
            $stmt->bindParam(":product_name", $data['product_name']);
            $stmt->bindParam(":unit", $data['unit']);
            $stmt->bindParam(":packing_size", $data['packing_size']);

            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                return floatval($row['product_qty']);
            }
            return 0;

        } catch (PDOException $e) {
            error_log("Error checking quantity: " . $e->getMessage());
            throw new Exception("Error checking product quantity");
        }
    }
    
    // Optional: Add a helper method to validate quantity against stock
    public function validateQuantity($data, $requestedQty) {
        try {
            $availableQty = $this->checkQuantityAvailable($data);
            
            if ($availableQty <= 0) {
                throw new Exception("Product is out of stock");
            }
            
            if ($requestedQty > $availableQty) {
                throw new Exception("Only {$availableQty} units available in stock");
            }
            
            return true;
    
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function checkDuplicateProduct($data) {
        $found = 0;
        foreach ($_SESSION['cart'] as $item) {
            if ($item['company_name'] === $data['company_name'] &&
                $item['product_name'] === $data['product_name'] &&
                $item['unit'] === $data['unit'] &&
                $item['packing_size'] === $data['packing_size']) {
                $found++;
            }
        }
        return $found;
    }

    private function getExistingQuantity($data) {
        foreach ($_SESSION['cart'] as $item) {
            if ($item['company_name'] === $data['company_name'] &&
                $item['product_name'] === $data['product_name'] &&
                $item['unit'] === $data['unit'] &&
                $item['packing_size'] === $data['packing_size']) {
                return floatval($item['qty']);
            }
        }
        return 0;
    }

    private function getProductSessionIndex($data) {
        foreach ($_SESSION['cart'] as $index => $item) {
            if ($item['company_name'] === $data['company_name'] &&
                $item['product_name'] === $data['product_name'] &&
                $item['unit'] === $data['unit'] &&
                $item['packing_size'] === $data['packing_size']) {
                return $index;
            }
        }
        return -1;
    }

    // Get the next bill number
    public function getNextBillNumber() {
        $query = "SELECT id FROM " . $this->billing_header . " ORDER BY id DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $next_id = 1;
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $next_id = $row['id'] + 1;
        }

        return str_pad($next_id, 5, '0', STR_PAD_LEFT);
    }

    // Get product price
    public function getProductPrice($data) {
        $query = "SELECT product_selling_price FROM " . $this->stock_table . "
                WHERE product_company = :company_name 
                AND product_name = :product_name 
                AND product_unit = :unit 
                AND packing_size = :packing_size";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":company_name", $data['company_name']);
        $stmt->bindParam(":product_name", $data['product_name']);
        $stmt->bindParam(":unit", $data['unit']);
        $stmt->bindParam(":packing_size", $data['packing_size']);
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return $row['product_selling_price'];
        }
        return 0;
    }

    
}


?>