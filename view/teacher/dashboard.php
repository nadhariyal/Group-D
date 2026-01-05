<?php
session_start();
require_once '../../config/database.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();


$query = "SELECT * FROM courses WHERE teacher_id = :teacher_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':teacher_id', $_SESSION['user_id']);
$stmt->execute();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);


$query = "SELECT COUNT(DISTINCT student_id) as total_students FROM enrollments e 
          JOIN courses c ON e.course_id = c.id 
          WHERE c.teacher_id = :teacher_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':teacher_id', $_SESSION['user_id']);
$stmt->execute();
$total_students = $stmt->fetch(PDO::FETCH_ASSOC)['total_students'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - University System</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="glass-container">
        <div class="glass-card">
            <div class="dashboard-header">
                <div class="user-info">
                    <h2>Welcome, <?php echo $_SESSION['full_name']; ?>!</h2>
                    <span class="user-role">Teacher</span>
                </div>
                <a href="../../index.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-book-open"></i>
                    <h3><?php echo count($courses); ?></h3>
                    <p>My Courses</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-user-graduate"></i>
                    <h3><?php echo $total_students; ?></h3>
                    <p>Total Students</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-tasks"></i>
                    <h3>12</h3>
                    <p>Assignments</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-calendar-alt"></i>
                    <h3>5</h3>
                    <p>Upcoming Classes</p>
                </div>
            </div>
            
            <h3 style="color: white; margin-bottom: 20px;">My Courses</h3>
            
            <?php if (empty($courses)): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> No courses assigned yet.
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Course Name</th>
                                <th>Credits</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($courses as $course): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($course['course_code']); ?></td>
                                <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                <td><?php echo $course['credits']; ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="manage_grades.php?course_id=<?php echo $course['id']; ?>" class="btn btn-success btn-sm">
                                            <i class="fas fa-graduation-cap"></i> Manage Grades
                                        </a>
                                        <a href="#" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i> Edit
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
                <a href="#" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create New Course
                </a>
                <a href="manage_grades.php" class="btn btn-success">
                    <i class="fas fa-graduation-cap"></i> Manage All Grades
                </a>
                <a href="#" class="btn btn-warning">
                    <i class="fas fa-calendar-plus"></i> Schedule Class
                </a>
            </div>
        </div>
    </div>
</body>
</html>