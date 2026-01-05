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


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['restore'])) {
    
    if (isset($_FILES['backup_file']) && $_FILES['backup_file']['error'] == 0) {
        $backup_file = $_FILES['backup_file']['tmp_name'];
        $file_type = pathinfo($_FILES['backup_file']['name'], PATHINFO_EXTENSION);
        
        if ($file_type == 'sql') {
            
            $sql_content = file_get_contents($backup_file);
            
            
            try {
                
                $db->exec("SET FOREIGN_KEY_CHECKS = 0");
                
            
                $db->exec($sql_content);
                
               
                $db->exec("SET FOREIGN_KEY_CHECKS = 1");
                
                $message = "Database restored successfully!";
                $message_type = "success";
                
            } catch (PDOException $e) {
                $message = "Error restoring database: " . $e->getMessage();
                $message_type = "danger";
            }
        } else {
            $message = "Invalid file type. Please upload a .sql file.";
            $message_type = "danger";
        }
    } else {
        $message = "Please select a backup file to restore.";
        $message_type = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restore Database - Admin Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="glass-container">
        <div class="glass-card">
            <div class="dashboard-header">
                <div class="user-info">
                    <h2><i class="fas fa-database"></i> Restore Database</h2>
                    <span class="user-role">Administrator</span>
                </div>
                <div>
                    <a href="system_settings.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Settings
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

            <div style="max-width: 600px; margin: 0 auto; text-align: center;">
                <div style="background: rgba(255, 193, 7, 0.1); padding: 30px; border-radius: 15px; margin-bottom: 30px;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 4rem; color: #ffc107; margin-bottom: 20px;"></i>
                    <h3 style="color: white; margin-bottom: 15px;">Warning!</h3>
                    <p style="color: rgba(255, 255, 255, 0.8);">
                        Restoring a database will <strong>overwrite all current data</strong> with the backup data.
                        This action cannot be undone.
                    </p>
                </div>

                <h3 style="color: white; margin-bottom: 20px;">
                    <i class="fas fa-upload"></i> Upload Backup File
                </h3>
                
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="backup_file" class="btn btn-warning" style="display: block; padding: 20px; cursor: pointer;">
                            <i class="fas fa-file-upload"></i> Choose SQL Backup File
                        </label>
                        <input type="file" id="backup_file" name="backup_file" accept=".sql" 
                               style="display: none;" onchange="previewFile(this)">
                        <div id="file-name" style="color: rgba(255, 255, 255, 0.7); margin-top: 10px;">
                            No file selected
                        </div>
                    </div>
                    
                    <div style="background: rgba(255, 255, 255, 0.1); padding: 20px; border-radius: 10px; margin: 20px 0;">
                        <h4 style="color: white; margin-bottom: 10px;">
                            <i class="fas fa-info-circle"></i> Instructions
                        </h4>
                        <ul style="color: rgba(255, 255, 255, 0.8); text-align: left; padding-left: 20px;">
                            <li>Only .sql files are accepted</li>
                            <li>Make sure you have a current backup before proceeding</li>
                            <li>Restoration may take several minutes</li>
                            <li>Do not close the browser during restoration</li>
                        </ul>
                    </div>
                    
                    <button type="submit" name="restore" class="btn btn-danger btn-lg"
                            onclick="return confirm('WARNING: This will overwrite all current data. Are you sure?')">
                        <i class="fas fa-play-circle"></i> Restore Database
                    </button>
                </form>
                
                <div style="margin-top: 40px; display: flex; gap: 15px; justify-content: center;">
                    <a href="backup_database.php" class="btn btn-success">
                        <i class="fas fa-download"></i> Create Backup First
                    </a>
                    <a href="system_settings.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function previewFile(input) {
            const fileName = document.getElementById('file-name');
            if (input.files && input.files[0]) {
                fileName.innerHTML = '<i class="fas fa-file"></i> Selected: ' + input.files[0].name;
                fileName.style.color = '#96c93d';
            } else {
                fileName.innerHTML = 'No file selected';
                fileName.style.color = 'rgba(255, 255, 255, 0.7)';
            }
        }
    </script>
</body>
</html>