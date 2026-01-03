
<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>InventaFlow</title>
    <link rel="stylesheet" href="../public/style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <?php include 'navbar.php'; ?>
    <div class="container">
        </div>

<form action="../controllers/login_handler.php" method="POST">
    <h2>InventaFlow Login</h2>
    <input type="text" name="username" placeholder="Enter Username" required>
    <input type="password" name="password" placeholder="Enter Password" required>
    <button type="submit">Login</button>
</form>
</body>
</html>