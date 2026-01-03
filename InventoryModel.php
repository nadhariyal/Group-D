<?php
class InventoryModel {
    private $conn;
    private $table = "products";

    public function __construct($db) {
        $this->conn = $db;
    }

    
    public function create($name, $category, $stock, $price, $supplier) {
        $query = "INSERT INTO " . $this->table . " (product_name, category, stock_level, price, supplier_name) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$name, $category, $stock, $price, $supplier]);
    }

    
    public function readAll() {
        $query = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
    public function update($id, $name, $stock, $price) {
        $query = "UPDATE " . $this->table . " SET product_name=?, stock_level=?, price=? WHERE id=?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$name, $stock, $price, $id]);
    }

    t
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id=?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }
}
?>