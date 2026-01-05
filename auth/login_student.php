<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $query = "SELECT * FROM users WHERE username = :username AND role = 'student'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    if ($stmt->rowCount() == 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            
            header("Location: ../student/dashboard.php");
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "Student not found!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login - University System</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="glass-container">
        <div class="glass-card">
            <h1 class="title"><i class="fas fa-user-graduate"></i> Student Login</h1>
            <p class="subtitle">Access student portal</p>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="form-container">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="username"><i class="fas fa-user-circle"></i> Username</label>
                        <input type="text" id="username" name="username" class="form-control" required 
                               placeholder="Enter student username">
                    </div>
                    
                    <div class="form-group">
                        <label for="password"><i class="fas fa-lock"></i> Password</label>
                        <input type="password" id="password" name="password" class="form-control" required 
                               placeholder="Enter your password">
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-sign-in-alt"></i> Login as Student
                    </button>
                </form>
                
                <div class="divider">
                    <span>OR</span>
                </div>
                
                <a href="../../index.php" class="btn btn-block">
                    <i class="fas fa-arrow-left"></i> Back to Role Selection
                </a>
            </div>
        </div>
    </div>
</body>
</html>