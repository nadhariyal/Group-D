<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $password;
    public $role;
    public $full_name;
    public $email;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET username=:username, password=:password, role=:role, 
                      full_name=:full_name, email=:email";
        
        $stmt = $this->conn->prepare($query);
        
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":email", $this->email);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }


    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET username=:username, role=:role, full_name=:full_name, email=:email 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':full_name', $this->full_name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':id', $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }


    public function login() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE username = :username AND role = :role";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':role', $this->role);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if(password_verify($this->password, $row['password'])) {
                $this->id = $row['id'];
                $this->full_name = $row['full_name'];
                $this->email = $row['email'];
                return true;
            }
        }
        return false;
    }
}
?>