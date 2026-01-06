<?php
session_start();
require_once '../../config/database.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();


$query = "SELECT c.*, e.enrollment_date, e.status 
          FROM enrollments e 
          JOIN courses c ON e.course_id = c.id 
          WHERE e.student_id = :student_id 
          ORDER BY e.enrollment_date DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':student_id', $_SESSION['user_id']);
$stmt->execute();
$enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);


$query = "SELECT c.course_name, g.grade 
          FROM grades g 
          JOIN courses c ON g.course_id = c.id 
          WHERE g.student_id = :student_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':student_id', $_SESSION['user_id']);
$stmt->execute();
$grades = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - University System</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="glass-container">
        <div class="glass-card">
            <div class="dashboard-header">
                <div class="user-info">
                    <h2>Welcome, <?php echo $_SESSION['full_name']; ?>!</h2>
                    <span class="user-role">Student</span>
                </div>
                <a href="../../index.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-book"></i>
                    <h3><?php echo count($enrollments); ?></h3>
                    <p>Enrolled Courses</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-graduation-cap"></i>
                    <h3><?php echo count($grades); ?></h3>
                    <p>Graded Courses</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-clock"></i>
                    <h3>15</h3>
                    <p>Study Hours</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-calendar-check"></i>
                    <h3>3</h3>
                    <p>Upcoming Exams</p>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 40px;">
                <div>
                    <h3 style="color: white; margin-bottom: 20px;">My Courses</h3>
                    <?php if (empty($enrollments)): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> No courses enrolled yet.
                        </div>
                    <?php else: ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Course</th>
                                        <th>Status</th>
                                        <th>Enrolled</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($enrollments as $enrollment): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($enrollment['course_code']); ?></strong><br>
                                            <?php echo htmlspecialchars($enrollment['course_name']); ?>
                                        </td>
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
                                        <td><?php echo $enrollment['enrollment_date']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div>
                    <h3 style="color: white; margin-bottom: 20px;">My Grades</h3>
                    <?php if (empty($grades)): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> No grades available yet.
                        </div>
                    <?php else: ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Course</th>
                                        <th>Grade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($grades as $grade): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($grade['course_name']); ?></td>
                                        <td>
                                            <span class="user-role" style="background: rgba(40, 167, 69, 0.3);">
                                                <?php echo htmlspecialchars($grade['grade']); ?>
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
            
            <div style="display: flex; gap: 15px;">
                <a href="my_courses.php" class="btn btn-primary">
                    <i class="fas fa-list"></i> View All Courses
                </a>
                <a href="#" class="btn btn-success">
                    <i class="fas fa-calendar"></i> View Schedule
                </a>
                <a href="#" class="btn btn-warning">
                    <i class="fas fa-file-invoice"></i> View Transcript
                </a>
            </div>
        </div>
    </div>
</body>
</html>