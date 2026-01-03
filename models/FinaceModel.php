<?php
class FinanceModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    
    public function addExpense($desc, $amt, $cat, $date) {
        $query = "INSERT INTO expenses (description, amount, category, expense_date) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$desc, $amt, $cat, $date]);
    }

    
    public function getAllExpenses() {
        $query = "SELECT * FROM expenses ORDER BY expense_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
    public function getFinancialSummary() {
        $summary = [];
        
        
        $salesQuery = "SELECT SUM(total_price) as total_revenue FROM sales_transactions";
        $stmt = $this->conn->prepare($salesQuery);
        $stmt->execute();
        $summary['revenue'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_revenue'] ?? 0;

       
        $expQuery = "SELECT SUM(amount) as total_expenses FROM expenses";
        $stmt = $this->conn->prepare($expQuery);
        $stmt->execute();
        $summary['expenses'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_expenses'] ?? 0;

        return $summary;
    }
}