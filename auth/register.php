<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    
    $query = "INSERT INTO users (username, password, role, full_name, email) 
              VALUES (:username, :password, :role, :full_name, :email)";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':role', $role);
    $stmt->bindParam(':full_name', $full_name);
    $stmt->bindParam(':email', $email);
    
    if ($stmt->execute()) {
        $success = "Registration successful! You can now login.";
    } else {
        $error = "Registration failed. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - University System</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="glass-container">
        <div class="glass-card">
            <h1 class="title"><i class="fas fa-user-plus"></i> Register New Account</h1>
            <p class="subtitle">Create your account by selecting your role</p>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                    <br>
                    <a href="../../index.php" class="btn btn-success" style="margin-top: 10px;">
                        <i class="fas fa-home"></i> Go to Home
                    </a>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="form-container">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="full_name"><i class="fas fa-user"></i> Full Name</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" required 
                               placeholder="Enter your full name">
                    </div>
                    
                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" id="email" name="email" class="form-control" required 
                               placeholder="Enter your email">
                    </div>
                    
                    <div class="form-group">
                        <label for="username"><i class="fas fa-user-circle"></i> Username</label>
                        <input type="text" id="username" name="username" class="form-control" required 
                               placeholder="Choose a username">
                    </div>
                    
                    <div class="form-group">
                        <label for="password"><i class="fas fa-lock"></i> Password</label>
                        <input type="password" id="password" name="password" class="form-control" required 
                               placeholder="Choose a password">
                    </div>
                    
                    <div class="form-group">
                        <label for="role"><i class="fas fa-user-tag"></i> Select Role</label>
                        <select id="role" name="role" class="form-control" required>
                            <option value="">-- Select Your Role --</option>
                            <option value="admin">Administrator</option>
                            <option value="teacher">Teacher</option>
                            <option value="student">Student</option>
                            <option value="registrar">Registrar</option>
                            <option value="librarian">Librarian</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-user-plus"></i> Register Account
                    </button>
                </form>
                
                <div class="divider">
                    <span>Already have an account?</span>
                </div>
                
                <a href="../../index.php" class="btn btn-block">
                    <i class="fas fa-sign-in-alt"></i> Back to Login
                </a>
            </div>
        </div>
    </div>
</body>
</html>