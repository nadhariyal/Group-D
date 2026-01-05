<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="glass-container">
        <div class="glass-card">
            <h1 class="title"><i class="fas fa-university"></i> University Management System</h1>
            <p class="subtitle">Select your role to login</p>
            
            <div class="role-grid">
                <a href="views/auth/login_admin.php" class="role-card admin">
                    <i class="fas fa-user-shield"></i>
                    <h3>Admin</h3>
                    <p>Full system access</p>
                </a>
                
                <a href="views/auth/login_teacher.php" class="role-card teacher">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <h3>Teacher</h3>
                    <p>Manage courses & grades</p>
                </a>
                
                <a href="views/auth/login_student.php" class="role-card student">
                    <i class="fas fa-user-graduate"></i>
                    <h3>Student</h3>
                    <p>View courses & grades</p>
                </a>
                
                <a href="views/auth/login_registrar.php" class="role-card registrar">
                    <i class="fas fa-clipboard-list"></i>
                    <h3>Registrar</h3>
                    <p>Manage enrollments</p>
                </a>
                
                <a href="views/auth/login_librarian.php" class="role-card librarian">
                    <i class="fas fa-book"></i>
                    <h3>Librarian</h3>
                    <p>Manage library resources</p>
                </a>
            </div>
            
            <div class="divider">
                <span>OR</span>
            </div>
            
            <a href="views/auth/register.php" class="btn-register">
                <i class="fas fa-user-plus"></i> Register New Account
            </a>
            
            <div class="footer">
                <p>University Management System &copy; 2026</p>
            </div>
        </div>
    </div>
</body>
</html>