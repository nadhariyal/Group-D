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
$optimization_results = [];


if (isset($_POST['optimize'])) {
    try {
        
        $tables_query = "SHOW TABLES";
        $tables_stmt = $db->prepare($tables_query);
        $tables_stmt->execute();
        $tables = $tables_stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($tables as $table) {
            
            $optimize_query = "OPTIMIZE TABLE `$table`";
            $optimize_stmt = $db->prepare($optimize_query);
            $optimize_stmt->execute();
            $result = $optimize_stmt->fetch(PDO::FETCH_ASSOC);
            
            $optimization_results[] = [
                'table' => $table,
                'result' => $result['Msg_text']
            ];
        }
        
        $message = "Database optimization completed successfully!";
        $message_type = "success";
        
    } catch (PDOException $e) {
        $message = "Error optimizing database: " . $e->getMessage();
        $message_type = "danger";
    }
}


if (isset($_POST['repair'])) {
    $table = $_POST['table_name'];
    
    try {
        $repair_query = "REPAIR TABLE `$table`";
        $repair_stmt = $db->prepare($repair_query);
        $repair_stmt->execute();
        $result = $repair_stmt->fetch(PDO::FETCH_ASSOC);
        
        $message = "Table '$table' repaired: " . $result['Msg_text'];
        $message_type = "success";
        
    } catch (PDOException $e) {
        $message = "Error repairing table: " . $e->getMessage();
        $message_type = "danger";
    }
}


$stats_query = "SELECT 
    table_schema as 'database',
    SUM(data_length + index_length) as 'size',
    SUM(data_length) as 'data_size',
    SUM(index_length) as 'index_size',
    COUNT(*) as 'table_count'
FROM information_schema.tables 
WHERE table_schema = DATABASE()
GROUP BY table_schema";

$stats_stmt = $db->prepare($stats_query);
$stats_stmt->execute();
$database_stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);


$tables_query = "SELECT 
    table_name,
    engine,
    table_rows,
    data_length,
    index_length,
    (data_length + index_length) as total_size,
    create_time,
    update_time
FROM information_schema.tables 
WHERE table_schema = DATABASE()
ORDER BY table_name";

$tables_stmt = $db->prepare($tables_query);
$tables_stmt->execute();
$tables = $tables_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Optimize Database - Admin Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="glass-container">
        <div class="glass-card">
            <div class="dashboard-header">
                <div class="user-info">
                    <h2><i class="fas fa-tools"></i> Optimize Database</h2>
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

            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
                
                <div>
                    <h3 style="color: white; margin-bottom: 20px;">
                        <i class="fas fa-chart-bar"></i> Database Statistics
                    </h3>
                    
                    <div style="background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                        <h4 style="color: white; margin-bottom: 15px;">
                            <i class="fas fa-database"></i> <?php echo $database_stats['database']; ?>
                        </h4>
                        
                        <div style="color: rgba(255,255,255,0.8);">
                            <p><strong>Total Size:</strong> <?php echo formatBytes($database_stats['size']); ?></p>
                            <p><strong>Data Size:</strong> <?php echo formatBytes($database_stats['data_size']); ?></p>
                            <p><strong>Index Size:</strong> <?php echo formatBytes($database_stats['index_size']); ?></p>
                            <p><strong>Tables:</strong> <?php echo $database_stats['table_count']; ?></p>
                        </div>
                    </div>
                    
                    <div style="background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px;">
                        <h4 style="color: white; margin-bottom: 15px;">
                            <i class="fas fa-cogs"></i> Quick Actions
                        </h4>
                        
                        <form method="POST" action="">
                            <button type="submit" name="optimize" class="btn btn-success btn-block" 
                                    onclick="return confirm('Optimize all database tables?')">
                                <i class="fas fa-bolt"></i> Optimize All Tables
                            </button>
                        </form>
                        
                        <div style="margin-top: 15px;">
                            <a href="analyze_tables.php" class="btn btn-info btn-block">
                                <i class="fas fa-search"></i> Analyze Tables
                            </a>
                        </div>
                        
                        <div style="margin-top: 15px;">
                            <a href="check_tables.php" class="btn btn-warning btn-block">
                                <i class="fas fa-check-circle"></i> Check Tables
                            </a>
                        </div>
                    </div>
                </div>

                
                <div>
                    <h3 style="color: white; margin-bottom: 20px;">
                        <i class="fas fa-table"></i> Database Tables
                    </h3>
                    
                    <?php if (empty($tables)): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> No tables found in database.
                        </div>
                    <?php else: ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Table Name</th>
                                        <th>Rows</th>
                                        <th>Size</th>
                                        <th>Engine</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tables as $table): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo $table['table_name']; ?></strong>
                                        </td>
                                        <td><?php echo number_format($table['table_rows']); ?></td>
                                        <td><?php echo formatBytes($table['total_size']); ?></td>
                                        <td>
                                            <span class="user-role" style="background: rgba(0, 123, 255, 0.3);">
                                                <?php echo $table['engine']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <form method="POST" action="" style="display: inline;">
                                                    <input type="hidden" name="table_name" value="<?php echo $table['table_name']; ?>">
                                                    <button type="submit" name="repair" class="btn btn-warning btn-sm">
                                                        <i class="fas fa-wrench"></i> Repair
                                                    </button>
                                                </form>
                                                <a href="optimize_table.php?table=<?php echo urlencode($table['table_name']); ?>" 
                                                   class="btn btn-success btn-sm">
                                                    <i class="fas fa-bolt"></i> Optimize
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if (!empty($optimization_results)): ?>
                        <div style="margin-top: 30px; background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px;">
                            <h4 style="color: white; margin-bottom: 15px;">
                                <i class="fas fa-clipboard-check"></i> Optimization Results
                            </h4>
                            <?php foreach ($optimization_results as $result): ?>
                                <p style="color: rgba(255,255,255,0.8); margin: 5px 0;">
                                    <strong><?php echo $result['table']; ?>:</strong> <?php echo $result['result']; ?>
                                </p>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}
?>