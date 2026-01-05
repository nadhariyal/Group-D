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


if (isset($_GET['backup'])) {
    
    $tables = array();
    $result = $db->query("SHOW TABLES");
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }
    
    
    $backup_file = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    $handle = fopen($backup_file, 'w+');
    
    
    foreach ($tables as $table) {
        
        fwrite($handle, "DROP TABLE IF EXISTS `$table`;\n\n");
        
        
        $result = $db->query("SHOW CREATE TABLE `$table`");
        $row = $result->fetch(PDO::FETCH_NUM);
        fwrite($handle, $row[1] . ";\n\n");
        
        
        $result = $db->query("SELECT * FROM `$table`");
        $num_fields = $result->columnCount();
        
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            fwrite($handle, "INSERT INTO `$table` VALUES(");
            for ($j = 0; $j < $num_fields; $j++) {
                $row[$j] = addslashes($row[$j]);
                $row[$j] = str_replace("\n", "\\n", $row[$j]);
                if (isset($row[$j])) {
                    fwrite($handle, '"' . $row[$j] . '"');
                } else {
                    fwrite($handle, '""');
                }
                if ($j < ($num_fields - 1)) {
                    fwrite($handle, ',');
                }
            }
            fwrite($handle, ");\n");
        }
        fwrite($handle, "\n\n");
    }
    
    fclose($handle);
    
    
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($backup_file) . '"');
    header('Content-Length: ' . filesize($backup_file));
    readfile($backup_file);
    
    
    unlink($backup_file);
    exit;
}


$backup_files = [];
if (is_dir('../../backups')) {
    $files = scandir('../../backups');
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) == 'sql') {
            $backup_files[] = $file;
        }
    }
    rsort($backup_files); 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Backup - Admin Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="glass-container">
        <div class="glass-card">
            <div class="dashboard-header">
                <div class="user-info">
                    <h2><i class="fas fa-database"></i> Database Backup</h2>
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

            <div style="text-align: center; margin: 40px 0;">
                <h3 style="color: white; margin-bottom: 20px;">Create Database Backup</h3>
                <p style="color: rgba(255,255,255,0.8); margin-bottom: 30px;">
                    This will create a complete backup of your database including all tables and data.
                </p>
                
                <a href="backup_database.php?backup=1" class="btn btn-success btn-lg" 
                   onclick="return confirm('Are you sure you want to create a database backup?')">
                    <i class="fas fa-download"></i> Create Backup Now
                </a>
                
                <div style="margin-top: 30px; color: rgba(255,255,255,0.7);">
                    <i class="fas fa-info-circle"></i> The backup file will be downloaded automatically.
                </div>
            </div>
            
            <?php if (!empty($backup_files)): ?>
            <div style="margin-top: 40px;">
                <h3 style="color: white; margin-bottom: 20px;">
                    <i class="fas fa-history"></i> Previous Backups
                </h3>
                
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Filename</th>
                                <th>Size</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($backup_files as $file): 
                                $filepath = '../../backups/' . $file;
                                if (file_exists($filepath)):
                            ?>
                            <tr>
                                <td><?php echo $file; ?></td>
                                <td><?php echo round(filesize($filepath) / 1024, 2); ?> KB</td>
                                <td><?php echo date('Y-m-d H:i:s', filemtime($filepath)); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="../../backups/<?php echo $file; ?>" class="btn btn-primary btn-sm" download>
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                        <a href="restore_database.php?file=<?php echo urlencode($file); ?>" 
                                           class="btn btn-warning btn-sm">
                                            <i class="fas fa-upload"></i> Restore
                                        </a>
                                        <a href="backup_database.php?delete=<?php echo urlencode($file); ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Delete this backup?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>