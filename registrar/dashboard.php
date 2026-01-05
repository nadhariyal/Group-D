<?php
session_start();
require_once '../../config/database.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'registrar') {
    header("Location: ../../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();


$query = "SELECT COUNT(*) as total_enrollments FROM enrollments";
$stmt = $db->prepare($query);
$stmt->execute();
$total_enrollments = $stmt->fetch(PDO::FETCH_ASSOC)['total_enrollments'];

$query = "SELECT COUNT(*) as active_enrollments FROM enrollments WHERE status = 'active'";
$stmt = $db->prepare($query);
$stmt->execute();
$active_enrollments = $stmt->fetch(PDO::FETCH_ASSOC)['active_enrollments'];


$query = "SELECT e.*, u.full_name as student_name, c.course_name 
          FROM enrollments e 
          JOIN users u ON e.student_id = u.id 
          JOIN courses c ON e.course_id = c.id 
          ORDER BY e.enrollment_date DESC LIMIT 10";
$stmt = $db->prepare($query);
$stmt->execute();
$recent_enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Dashboard - University System</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="glass-container">
        <div class="glass-card">
            <div class="dashboard-header">
                <div class="user-info">
                    <h2>Welcome, <?php echo $_SESSION['full_name']; ?>!</h2>
                    <span class="user-role">Registrar</span>
                </div>
                <a href="../../index.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-clipboard-list"></i>
                    <h3><?php echo $total_enrollments; ?></h3>
                    <p>Total Enrollments</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-user-check"></i>
                    <h3><?php echo $active_enrollments; ?></h3>
                    <p>Active Enrollments</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-user-graduate"></i>
                    <h3>150</h3>
                    <p>Total Students</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-book"></i>
                    <h3>25</h3>
                    <p>Available Courses</p>
                </div>
            </div>
            
            <h3 style="color: white; margin-bottom: 20px;">Recent Enrollments</h3>
            
            <?php if (empty($recent_enrollments)): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> No enrollments found.
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Course</th>
                                <th>Enrollment Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_enrollments as $enrollment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($enrollment['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($enrollment['course_name']); ?></td>
                                <td><?php echo $enrollment['enrollment_date']; ?></td>
                                <td>
                                    <span class="user-role" style="background: <?php 
                                        switch($enrollment['status']) {
                                            case 'active': echo 'rgba(40, 167, 69, 0.3)'; break;
                                            case 'completed': echo 'rgba(0, 123, 255, 0.3)'; break;
                                            case 'dropped': echo 'rgba(220, 53, 69, 0.3)'; break;
                                        }
                                    ?>;">
                                        <?php echo ucfirst($enrollment['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="#" class="btn btn-success btn-sm">
                                            <i class="fas fa-edit"></i> Update
                                        </a>
                                        <a href="#" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i> Remove
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            
            <div style="margin-top: 30px; display: flex; gap: 15px;">
                <a href="manage_enrollments.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Manage Enrollments
                </a>
                <a href="#" class="btn btn-success">
                    <i class="fas fa-file-export"></i> Generate Reports
                </a>
                <a href="#" class="btn btn-warning">
                    <i class="fas fa-print"></i> Print Transcripts
                </a>
            </div>
        </div>
    </div>
</body>
</html>