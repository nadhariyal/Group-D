<?php
session_start();
require_once '../../config/database.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$message = "";
$message_type = "";


if (isset($_GET['approve'])) {
    $user_id = $_GET['approve'];
    
    $update_query = "UPDATE users SET status = 'active' WHERE id = :id";
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bindParam(':id', $user_id);
    
    if ($update_stmt->execute()) {
        $message = "User approved successfully!";
        $message_type = "success";
    }
}


if (isset($_GET['reject'])) {
    $user_id = $_GET['reject'];
    
    $update_query = "UPDATE users SET status = 'blocked' WHERE id = :id";
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bindParam(':id', $user_id);
    
    if ($update_stmt->execute()) {
        $message = "User rejected!";
        $message_type = "success";
    }
}


$query = "SELECT * FROM users WHERE status = 'pending' ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$pending_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Users - Admin Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="glass-container">
        <div class="glass-card">
            <div class="dashboard-header">
                <div class="user-info">
                    <h2><i class="fas fa-user-check"></i> Approve Users</h2>
                    <span class="user-role">Administrator</span>
                </div>
                <div>
                    <a href="dashboard.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                    <a href="../../index.php" class="btn btn-danger">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <h3 style="color: white; margin-bottom: 20px;">
                <i class="fas fa-clock"></i> Pending Approvals (<?php echo count($pending_users); ?>)
            </h3>
            
            <?php if (empty($pending_users)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> No pending approvals.
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_users as $user): ?>
                            <tr>
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
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="approve_users.php?approve=<?php echo $user['id']; ?>" 
                                           class="btn btn-success btn-sm">
                                            <i class="fas fa-check"></i> Approve
                                        </a>
                                        <a href="approve_users.php?reject=<?php echo $user['id']; ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Reject this user?')">
                                            <i class="fas fa-times"></i> Reject
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            
            <div style="margin-top: 30px; background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px;">
                <h4 style="color: white; margin-bottom: 10px;">
                    <i class="fas fa-info-circle"></i> Security Policy
                </h4>
                <p style="color: rgba(255,255,255,0.8);">
                    • Admin registrations require approval<br>
                    • Only existing admins can approve new admins<br>
                    • Other roles (teacher, student, etc.) are auto-approved<br>
                    • Rejected users cannot login
                </p>
            </div>
        </div>
    </div>
</body>
</html>