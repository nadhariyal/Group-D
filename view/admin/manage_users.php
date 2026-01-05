<?php
session_start();
require_once '../../config/database.php';
require_once '../../models/User.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$message = "";
$message_type = "";


if (isset($_POST['add_user'])) {
    $user->username = $_POST['username'];
    $user->password = $_POST['password'];
    $user->role = $_POST['role'];
    $user->full_name = $_POST['full_name'];
    $user->email = $_POST['email'];
    
    if ($user->create()) {
        $message = "User added successfully!";
        $message_type = "success";
    } else {
        $message = "Failed to add user.";
        $message_type = "danger";
    }
}


if (isset($_POST['update_user'])) {
    $user->id = $_POST['user_id'];
    $user->username = $_POST['username'];
    $user->role = $_POST['role'];
    $user->full_name = $_POST['full_name'];
    $user->email = $_POST['email'];
    
    if ($user->update()) {
        $message = "User updated successfully!";
        $message_type = "success";
    } else {
        $message = "Failed to update user.";
        $message_type = "danger";
    }
}


if (isset($_GET['delete'])) {
    $user->id = $_GET['delete'];
    
    if ($user->delete()) {
        $message = "User deleted successfully!";
        $message_type = "success";
    } else {
        $message = "Failed to delete user.";
        $message_type = "danger";
    }
}


$stmt = $user->read();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);


$edit_user = null;
if (isset($_GET['edit'])) {
    $user->id = $_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user->id]);
    $edit_user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="glass-container">
        <div class="glass-card">
            <div class="dashboard-header">
                <div class="user-info">
                    <h2><i class="fas fa-users-cog"></i> Manage Users</h2>
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

            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
                
                <div>
                    <h3 style="color: white; margin-bottom: 20px;">
                        <i class="fas fa-<?php echo $edit_user ? 'edit' : 'user-plus'; ?>"></i>
                        <?php echo $edit_user ? 'Edit User' : 'Add New User'; ?>
                    </h3>
                    
                    <form method="POST" action="">
                        <?php if ($edit_user): ?>
                            <input type="hidden" name="user_id" value="<?php echo $edit_user['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="full_name"><i class="fas fa-user"></i> Full Name</label>
                            <input type="text" id="full_name" name="full_name" class="form-control" required 
                                   value="<?php echo $edit_user ? $edit_user['full_name'] : ''; ?>"
                                   placeholder="Enter full name">
                        </div>
                        
                        <div class="form-group">
                            <label for="email"><i class="fas fa-envelope"></i> Email</label>
                            <input type="email" id="email" name="email" class="form-control" required 
                                   value="<?php echo $edit_user ? $edit_user['email'] : ''; ?>"
                                   placeholder="Enter email">
                        </div>
                        
                        <div class="form-group">
                            <label for="username"><i class="fas fa-user-circle"></i> Username</label>
                            <input type="text" id="username" name="username" class="form-control" required 
                                   value="<?php echo $edit_user ? $edit_user['username'] : ''; ?>"
                                   placeholder="Choose username">
                        </div>
                        
                        <?php if (!$edit_user): ?>
                        <div class="form-group">
                            <label for="password"><i class="fas fa-lock"></i> Password</label>
                            <input type="password" id="password" name="password" class="form-control" required 
                                   placeholder="Enter password">
                        </div>
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="role"><i class="fas fa-user-tag"></i> Role</label>
                            <select id="role" name="role" class="form-control" required>
                                <option value="">-- Select Role --</option>
                                <option value="admin" <?php echo ($edit_user && $edit_user['role'] == 'admin') ? 'selected' : ''; ?>>Administrator</option>
                                <option value="teacher" <?php echo ($edit_user && $edit_user['role'] == 'teacher') ? 'selected' : ''; ?>>Teacher</option>
                                <option value="student" <?php echo ($edit_user && $edit_user['role'] == 'student') ? 'selected' : ''; ?>>Student</option>
                                <option value="registrar" <?php echo ($edit_user && $edit_user['role'] == 'registrar') ? 'selected' : ''; ?>>Registrar</option>
                                <option value="librarian" <?php echo ($edit_user && $edit_user['role'] == 'librarian') ? 'selected' : ''; ?>>Librarian</option>
                            </select>
                        </div>
                        
                        <button type="submit" name="<?php echo $edit_user ? 'update_user' : 'add_user'; ?>" 
                                class="btn btn-<?php echo $edit_user ? 'success' : 'primary'; ?> btn-block">
                            <i class="fas fa-<?php echo $edit_user ? 'save' : 'plus'; ?>"></i>
                            <?php echo $edit_user ? 'Update User' : 'Add User'; ?>
                        </button>
                        
                        <?php if ($edit_user): ?>
                            <a href="manage_users.php" class="btn btn-warning btn-block" style="margin-top: 10px;">
                                <i class="fas fa-times"></i> Cancel Edit
                            </a>
                        <?php endif; ?>
                    </form>
                </div>

                
                <div>
                    <h3 style="color: white; margin-bottom: 20px;">
                        <i class="fas fa-list"></i> All Users (<?php echo count($users); ?>)
                    </h3>
                    
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                <tr>
                                    <td><?php echo $u['id']; ?></td>
                                    <td><?php echo htmlspecialchars($u['username']); ?></td>
                                    <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td>
                                        <span class="user-role" style="background: <?php 
                                            switch($u['role']) {
                                                case 'admin': echo 'rgba(220, 53, 69, 0.3)'; break;
                                                case 'teacher': echo 'rgba(40, 167, 69, 0.3)'; break;
                                                case 'student': echo 'rgba(0, 123, 255, 0.3)'; break;
                                                case 'registrar': echo 'rgba(255, 193, 7, 0.3)'; break;
                                                case 'librarian': echo 'rgba(111, 66, 193, 0.3)'; break;
                                            }
                                        ?>;">
                                            <?php echo ucfirst($u['role']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="manage_users.php?edit=<?php echo $u['id']; ?>" class="btn btn-success btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="manage_users.php?delete=<?php echo $u['id']; ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Are you sure you want to delete this user?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div style="margin-top: 20px; display: flex; gap: 10px; justify-content: space-between;">
                        <div>
                            <a href="export_users.php" class="btn btn-success">
                                <i class="fas fa-file-export"></i> Export to CSV
                            </a>
                            <a href="export_users.php?format=pdf" class="btn btn-danger">
                                <i class="fas fa-file-pdf"></i> Export to PDF
                            </a>
                        </div>
                        <div>
                            <a href="#" class="btn btn-warning">
                                <i class="fas fa-print"></i> Print List
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function confirmDelete(userId) {
            if (confirm("Are you sure you want to delete this user?")) {
                window.location.href = "manage_users.php?delete=" + userId;
            }
        }
    </script>
</body>
</html>