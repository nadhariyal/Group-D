
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
<form action="../controllers/UserController.php" method="POST">
    <h2>User Registration</h2>
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    
    <label>Select Your Role:</label>
    <select name="role_id">
        <option value="1">System Manager</option>
        <option value="2">Inventory Clerk</option>
        <option value="3">Sales Associate</option>
        <option value="4">Service Technician</option>
        <option value="5">Accountant</option>
    </select>
    
    <button type="submit">Register</button>
</form>
</body>
</html>