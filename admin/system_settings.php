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


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    
    $message = "Settings updated successfully!";
    $message_type = "success";
    
    
    if (isset($_POST['site_title'])) {
        
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings - Admin Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="glass-container">
        <div class="glass-card">
            <div class="dashboard-header">
                <div class="user-info">
                    <h2><i class="fas fa-cog"></i> System Settings</h2>
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

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                
                <div>
                    <h3 style="color: white; margin-bottom: 20px;">
                        <i class="fas fa-globe"></i> General Settings
                    </h3>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="site_title"><i class="fas fa-heading"></i> Site Title</label>
                            <input type="text" id="site_title" name="site_title" class="form-control" 
                                   value="University Management System" placeholder="Enter site title">
                        </div>
                        
                        <div class="form-group">
                            <label for="site_email"><i class="fas fa-envelope"></i> System Email</label>
                            <input type="email" id="site_email" name="site_email" class="form-control" 
                                   value="admin@university.edu" placeholder="Enter system email">
                        </div>
                        
                        <div class="form-group">
                            <label for="timezone"><i class="fas fa-clock"></i> Timezone</label>
                            <select id="timezone" name="timezone" class="form-control">
                                <option value="UTC" selected>UTC</option>
                                <option value="America/New_York">America/New_York</option>
                                <option value="Europe/London">Europe/London</option>
                                <option value="Asia/Kolkata">Asia/Kolkata</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="date_format"><i class="fas fa-calendar"></i> Date Format</label>
                            <select id="date_format" name="date_format" class="form-control">
                                <option value="Y-m-d" selected>YYYY-MM-DD</option>
                                <option value="d/m/Y">DD/MM/YYYY</option>
                                <option value="m/d/Y">MM/DD/YYYY</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-save"></i> Save General Settings
                        </button>
                    </form>
                </div>

                
                <div>
                    <h3 style="color: white; margin-bottom: 20px;">
                        <i class="fas fa-shield-alt"></i> Security Settings
                    </h3>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="password_policy">
                                <i class="fas fa-lock"></i> Password Policy
                            </label>
                            <select id="password_policy" name="password_policy" class="form-control">
                                <option value="low">Low (6+ characters)</option>
                                <option value="medium" selected>Medium (8+ characters with mixed case)</option>
                                <option value="high">High (10+ characters with special symbols)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="session_timeout">
                                <i class="fas fa-hourglass-half"></i> Session Timeout (minutes)
                            </label>
                            <input type="number" id="session_timeout" name="session_timeout" class="form-control" 
                                   value="30" min="5" max="240">
                        </div>
                        
                        <div class="form-group">
                            <label for="max_login_attempts">
                                <i class="fas fa-ban"></i> Max Login Attempts
                            </label>
                            <input type="number" id="max_login_attempts" name="max_login_attempts" class="form-control" 
                                   value="5" min="1" max="10">
                        </div>
                        
                        <div class="form-group">
                            <div style="color: white; margin-bottom: 10px;">
                                <i class="fas fa-check-circle"></i> Security Features
                            </div>
                            <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px;">
                                <label style="display: block; color: rgba(255,255,255,0.9); margin-bottom: 10px;">
                                    <input type="checkbox" name="enable_2fa" style="margin-right: 10px;">
                                    Enable Two-Factor Authentication
                                </label>
                                <label style="display: block; color: rgba(255,255,255,0.9); margin-bottom: 10px;">
                                    <input type="checkbox" name="force_ssl" checked style="margin-right: 10px;">
                                    Force SSL/HTTPS
                                </label>
                                <label style="display: block; color: rgba(255,255,255,0.9);">
                                    <input type="checkbox" name="login_logging" checked style="margin-right: 10px;">
                                    Enable Login Logging
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-warning btn-block">
                            <i class="fas fa-shield-alt"></i> Save Security Settings
                        </button>
                    </form>
                </div>
            </div>
            
            
            <div style="margin-top: 40px;">
                <h3 style="color: white; margin-bottom: 20px;">
                    <i class="fas fa-database"></i> Database Management
                </h3>
                
                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <a href="backup_database.php" class="btn btn-success">
                        <i class="fas fa-download"></i> Backup Database
                    </a>
                    <a href="restore_database.php" class="btn btn-warning">
                        <i class="fas fa-upload"></i> Restore Database
                    </a>
                    <a href="optimize_database.php" class="btn btn-info">
                        <i class="fas fa-tools"></i> Optimize Database
                    </a>
                    <a href="database_logs.php" class="btn btn-secondary">
                        <i class="fas fa-history"></i> View Logs
                    </a>
                </div>
                
                <div style="margin-top: 20px; background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px;">
                    <h4 style="color: white; margin-bottom: 15px;">
                        <i class="fas fa-info-circle"></i> System Information
                    </h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                        <div>
                            <p style="color: rgba(255,255,255,0.8); margin: 5px 0;">PHP Version: <?php echo phpversion(); ?></p>
                            <p style="color: rgba(255,255,255,0.8); margin: 5px 0;">MySQL Version: 
                                <?php 
                                $stmt = $db->query("SELECT VERSION() as version");
                                echo $stmt->fetch()['version'];
                                ?>
                            </p>
                        </div>
                        <div>
                            <p style="color: rgba(255,255,255,0.8); margin: 5px 0;">Server: <?php echo $_SERVER['SERVER_SOFTWARE']; ?></p>
                            <p style="color: rgba(255,255,255,0.8); margin: 5px 0;">Max Upload: <?php echo ini_get('upload_max_filesize'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>