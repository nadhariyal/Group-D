<?php
class TicketModel {
    private $conn;
    private $table = "service_tickets";

    public function __construct($db) {
        $this->conn = $db;
    }

    // CREATE: Open a new service ticket
    public function createTicket($customer, $description, $tech_id) {
        $query = "INSERT INTO " . $this->table . " (customer_name, issue_description, assigned_tech_id) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$customer, $description, $tech_id]);
    }

    // READ: Get all tickets (Complex Join)
    public function getAllTickets() {
        $query = "SELECT t.*, u.username as technician FROM " . $this->table . " t 
                  LEFT JOIN users u ON t.assigned_tech_id = u.id ORDER BY t.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // UPDATE: Update ticket status
    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table . " SET status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$status, $id]);
    }

    // DELETE: Remove a ticket
    public function deleteTicket($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return
    }
}
