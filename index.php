<?php 
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InventaFlow - System</title>
    <link rel="stylesheet" href="public/style.css">
    <style>
        .hero-section {
            height: 80vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            background-color: #f4f4f4;
        }
        .btn-group {
            margin-top: 20px;
        }
        .btn {
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 10px;
            display: inline-block;
        }
        .btn-login { background-color: #000; color: #fff; }
        .btn-register { border: 2px solid #000; color: #000; }
    </style>
</head>
<body>

    <div class="hero-section">
        <h1>Welcome to InventaFlow</h1>
        <p>The Unified Inventory & Service Management System for Modern Businesses.</p>
        
        <div class="btn-group">
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="views/admin_dashboard.php" class="btn btn-login">Go to Dashboard</a>
            <?php else: ?>
                <a href="views/login.php" class="btn btn-login">Login to System</a>
                <a href="views/register.php" class="btn btn-register">Create Account</a>
            <?php endif; ?>
        </div>
    </div>

    <footer style="text-align: center; padding: 20px; font-size: 0.9em; color: #666;">
        &copy; 2026 InventaFlow Team - HND IT, ATI-BATTICALOA

</body>
</html>