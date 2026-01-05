<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'registrar') {
    header("Location: ../../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();


$stats_query = "SELECT 
    COUNT(*) as total_enrollments,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_enrollments,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_enrollments,
    SUM(CASE WHEN status = 'dropped' THEN 1 ELSE 0 END) as dropped_enrollments
FROM enrollments";
$stats_stmt = $db->prepare($stats_query);
$stats_stmt->execute();
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);


$course_query = "SELECT c.course_code, c.course_name, COUNT(e.id) as enrollment_count
                 FROM courses c 
                 LEFT JOIN enrollments e ON c.id = e.course_id 
                 GROUP BY c.id 
                 ORDER BY enrollment_count DESC";
$course_stmt = $db->prepare($course_query);
$course_stmt->execute();
$course_stats = $course_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Reports - Registrar Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="glass-container">
        <div class="glass-card">
            <div class="dashboard-header">
                <div class="user-info">
                    <h2><i class="fas fa-chart-bar"></i> Generate Reports</h2>
                    <span class="user-role">Registrar</span>
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

            <div class="stats-grid" style="margin-bottom: 30px;">
                <div class="stat-card">
                    <i class="fas fa-clipboard-list"></i>
                    <h3><?php echo $stats['total_enrollments']; ?></h3>
                    <p>Total Enrollments</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-user-check"></i>
                    <h3><?php echo $stats['active_enrollments']; ?></h3>
                    <p>Active</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-graduation-cap"></i>
                    <h3><?php echo $stats['completed_enrollments']; ?></h3>
                    <p>Completed</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-times-circle"></i>
                    <h3><?php echo $stats['dropped_enrollments']; ?></h3>
                    <p>Dropped</p>
                </div>
            </div>

            <h3 style="color: white; margin-bottom: 20px;">
                <i class="fas fa-book"></i> Course Enrollment Statistics
            </h3>
            
            <?php if (empty($course_stats)): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> No course data available.
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Course Name</th>
                                <th>Enrollment Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($course_stats as $course): ?>
                            <tr>
                                <td><?php echo $course['course_code']; ?></td>
                                <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                <td>
                                    <span class="user-role" style="background: rgba(0, 123, 255, 0.3);">
                                        <?php echo $course['enrollment_count']; ?> students
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>