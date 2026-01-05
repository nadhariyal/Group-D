<?php
session_start();
require_once '../../config/database.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();


$query = "SELECT COUNT(*) as total_users FROM users";
$stmt = $db->prepare($query);
$stmt->execute();
$total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

$query = "SELECT COUNT(*) as total_courses FROM courses";
$stmt = $db->prepare($query);
$stmt->execute();
$total_courses = $stmt->fetch(PDO::FETCH_ASSOC)['total_courses'];


$query = "SELECT * FROM users ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - University System</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="glass-container">
        <div class="glass-card">
            <div class="dashboard-header">
                <div class="user-info">
                    <h2>Welcome, <?php echo $_SESSION['full_name']; ?>!</h2>
                    <span class="user-role">Administrator</span>
                </div>
                <a href="../../index.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <h3><?php echo $total_users; ?></h3>
                    <p>Total Users</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-book"></i>
                    <h3><?php echo $total_courses; ?></h3>
                    <p>Total Courses</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-chart-line"></i>
                    <h3>5</h3>
                    <p>User Roles</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-database"></i>
                    <h3>6</h3>
                    <p>Database Tables</p>
                </div>
            </div>
            
            <h3 style="color: white; margin-bottom: 20px;">User Management</h3>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="user-role" style="background: <?php 
                                    switch($user['role']) {
                                        case 'admin': echo 'rgba(220, 53, 69, 0.3)'; break;
                                        case 'teacher': echo 'rgba(40, 167, 69, 0.3)'; break;
                                        case 'student': echo 'rgba(0, 123, 255, 0.3)'; break;
                                        case 'registrar': echo 'rgba(255, 193, 7, 0.3)'; break;
                                        case 'librarian': echo 'rgba(111, 66, 193, 0.3)'; break;
                                    }
                                ?>;">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td><?php echo $user['created_at']; ?></td>
                            <td>
                                <td>
    <div class="action-buttons">
        <a href="manage_users.php?edit=<?php echo $user['id']; ?>" class="btn btn-success btn-sm">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="manage_users.php?delete=<?php echo $user['id']; ?>" 
           class="btn btn-danger btn-sm"
           onclick="return confirm('Are you sure you want to delete this user?')">
            <i class="fas fa-trash"></i> Delete
        </a>
    </div>
</td>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
                        <div style="margin-top: 30px; display: flex; gap: 15px; flex-wrap: wrap;">
                <a href="manage_users.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New User
                </a>
                <a href="manage_users.php" class="btn btn-success">
                    <i class="fas fa-users-cog"></i> Manage Users
                </a>
                <a href="approve_users.php" class="btn btn-warning">
                    <i class="fas fa-user-check"></i> Approve Users
                </a>
                <a href="export_users.php" class="btn btn-info">
                    <i class="fas fa-file-export"></i> Export Data
                </a>
                <a href="system_settings.php" class="btn btn-secondary">
                    <i class="fas fa-cog"></i> System Settings
                </a>
            </div>
        </div>
    </div>
</body>
</html>