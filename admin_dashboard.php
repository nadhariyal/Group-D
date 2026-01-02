<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'System Manager') { 
    header("Location: login.php"); 
    exit(); 
}

require_once '../config/Database.php';
require_once '../models/UserModel.php';

$database = new Database();
$db = $database->getConnection();
$userModel = new UserModel($db);


$query = "SELECT u.id, u.username, r.role_name FROM users u JOIN roles r ON u.role_id = r.id";
$stmt = $db->prepare($query);
$stmt->execute();
$all_users = $stmt->fetchAll(PDO::FETCH_ASSOC);


if (isset($_GET['delete_id'])) {
    $del_query = "DELETE FROM users WHERE id = ?";
    $del_stmt = $db->prepare($del_query);
    $del_stmt->execute([$_GET['delete_id']]);
    header("Location: admin_dashboard.php");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>System Manager - Admin Dashboard</title>
    <link rel="stylesheet" href="../public/style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <h2>System Manager Dashboard</h2>
        <p>Welcome, <?php echo $_SESSION['role']; ?> | <a href="logout.php" class="btn-delete">Logout</a></p>
        
        <hr>
        
        <h3>Manage System Users</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_users as $user): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo $user['username']; ?></td>
                    <td><?php echo $user['role_name']; ?></td>
                    <td>
                        <a href="admin_dashboard.php?delete_id=<?php echo $user['id']; ?>" 
                           class="btn-delete" 
                           onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <br>
        <p><a href="register.php">Add New User (Create)</a></p>
    </div>
</body>
</html>