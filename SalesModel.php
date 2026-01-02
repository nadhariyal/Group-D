<?php
class SalesModel {
    private $conn;
    private $table = "sales_transactions";

    public function __construct($db) {
        $this->conn = $db;
    }

    
    public function createSale($customer, $product_id, $qty, $total) {
        $query = "INSERT INTO " . $this->table . " (customer_name, product_id, quantity, total_price) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$customer, $product_id, $qty, $total]);
    }

    public function getAllSales() {
        $query = "SELECT s.*, p.product_name FROM " . $this->table . " s 
                  JOIN products p ON s.product_id = p.id ORDER BY s.transaction_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteSale($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }
}
?>