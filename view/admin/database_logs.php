<?php
session_start();
require_once '../../config/database.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();


$create_table = "CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    username VARCHAR(50) NOT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";
$db->exec($create_table);


$query = "SELECT al.*, u.full_name 
          FROM activity_logs al 
          JOIN users u ON al.user_id = u.id 
          ORDER BY al.created_at DESC 
          LIMIT 100";
$stmt = $db->prepare($query);
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);


$stats_query = "SELECT 
    DATE(created_at) as date,
    COUNT(*) as count,
    GROUP_CONCAT(DISTINCT action SEPARATOR ', ') as actions
FROM activity_logs 
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY DATE(created_at)
ORDER BY date DESC";

$stats_stmt = $db->prepare($stats_query);
$stats_stmt->execute();
$stats = $stats_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Logs - Admin Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="glass-container">
        <div class="glass-card">
            <div class="dashboard-header">
                <div class="user-info">
                    <h2><i class="fas fa-history"></i> Database Logs</h2>
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

            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
                
                <div>
                    <h3 style="color: white; margin-bottom: 20px;">
                        <i class="fas fa-chart-pie"></i> Activity Statistics
                    </h3>
                    
                    <div style="background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                        <h4 style="color: white; margin-bottom: 15px;">
                            <i class="fas fa-calendar-week"></i> Last 7 Days
                        </h4>
                        
                        <?php if (empty($stats)): ?>
                            <p style="color: rgba(255,255,255,0.7); text-align: center;">
                                No activity data available.
                            </p>
                        <?php else: ?>
                            <?php foreach ($stats as $stat): ?>
                            <div style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,0.1);">
                                <div style="display: flex; justify-content: space-between;">
                                    <span style="color: white; font-weight: bold;">
                                        <?php echo date('D, M j', strtotime($stat['date'])); ?>
                                    </span>
                                    <span class="user-role" style="background: rgba(40, 167, 69, 0.3);">
                                        <?php echo $stat['count']; ?> actions
                                    </span>
                                </div>
                                <p style="color: rgba(255,255,255,0.7); font-size: 0.9rem; margin-top: 5px;">
                                    <?php echo $stat['actions']; ?>
                                </p>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div style="background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px;">
                        <h4 style="color: white; margin-bottom: 15px;">
                            <i class="fas fa-filter"></i> Filter Logs
                        </h4>
                        
                        <form method="GET" action="">
                            <div class="form-group">
                                <label for="date_from"><i class="fas fa-calendar-alt"></i> Date From</label>
                                <input type="date" id="date_from" name="date_from" class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label for="date_to"><i class="fas fa-calendar-alt"></i> Date To</label>
                                <input type="date" id="date_to" name="date_to" class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label for="action"><i class="fas fa-cogs"></i> Action Type</label>
                                <select id="action" name="action" class="form-control">
                                    <option value="">All Actions</option>
                                    <option value="login">Login</option>
                                    <option value="logout">Logout</option>
                                    <option value="create">Create</option>
                                    <option value="update">Update</option>
                                    <option value="delete">Delete</option>
                                    <option value="export">Export</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-info btn-block">
                                <i class="fas fa-search"></i> Filter
                            </button>
                        </form>
                    </div>
                </div>

                
                <div>
                    <h3 style="color: white; margin-bottom: 20px;">
                        <i class="fas fa-list"></i> Recent Activity (<?php echo count($logs); ?>)
                    </h3>
                    
                    <?php if (empty($logs)): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> No activity logs found.
                        </div>
                    <?php else: ?>
                        <div style="max-height: 600px; overflow-y: auto;">
                            <?php foreach ($logs as $log): 
                                $action_color = getActionColor($log['action']);
                            ?>
                            <div style="background: rgba(255,255,255,0.05); padding: 15px; margin-bottom: 10px; border-radius: 10px; 
                                        border-left: 4px solid <?php echo $action_color; ?>;">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                    <div>
                                        <strong style="color: white;"><?php echo htmlspecialchars($log['full_name']); ?></strong>
                                        <span class="user-role" style="background: <?php echo $action_color; ?>30; margin-left: 10px;">
                                            <?php echo ucfirst($log['action']); ?>
                                        </span>
                                        <div style="color: rgba(255,255,255,0.7); font-size: 0.9rem; margin-top: 5px;">
                                            <?php echo htmlspecialchars($log['details']); ?>
                                        </div>
                                    </div>
                                    <div style="text-align: right;">
                                        <div style="color: rgba(255,255,255,0.6); font-size: 0.8rem;">
                                            <?php echo date('M j, g:i a', strtotime($log['created_at'])); ?>
                                        </div>
                                        <?php if ($log['ip_address']): ?>
                                        <div style="color: rgba(255,255,255,0.5); font-size: 0.75rem; margin-top: 5px;">
                                            <i class="fas fa-network-wired"></i> <?php echo $log['ip_address']; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div style="margin-top: 20px; display: flex; gap: 10px; justify-content: center;">
                            <a href="export_logs.php" class="btn btn-success">
                                <i class="fas fa-file-export"></i> Export Logs
                            </a>
                            <button type="button" class="btn btn-danger" onclick="confirmClearLogs()">
                                <i class="fas fa-trash"></i> Clear Old Logs
                            </button>
                            <a href="system_logs.php" class="btn btn-info">
                                <i class="fas fa-server"></i> System Logs
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function confirmClearLogs() {
            if (confirm('Clear logs older than 30 days? This action cannot be undone.')) {
                window.location.href = 'clear_logs.php';
            }
        }
        
        
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            const weekAgo = new Date();
            weekAgo.setDate(weekAgo.getDate() - 7);
            const weekAgoStr = weekAgo.toISOString().split('T')[0];
            
            document.getElementById('date_from').value = weekAgoStr;
            document.getElementById('date_to').value = today;
        });
    </script>
</body>
</html>

<?php

function getActionColor($action) {
    switch (strtolower($action)) {
        case 'login':
            return '#28a745'; 
        case 'logout':
            return '#6c757d'; 
        case 'create':
            return '#007bff'; 
        case 'update':
            return '#ffc107'; 
        case 'delete':
            return '#dc3545'; 
        case 'export':
            return '#17a2b8'; 
        default:
            return '#6f42c1'; 
    }
}
?>