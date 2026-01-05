<?php
session_start();
require_once '../../config/database.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$query = "SELECT c.*, 
          (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id AND status = 'active') as student_count
          FROM courses c 
          WHERE c.teacher_id = :teacher_id 
          ORDER BY c.course_code";
$stmt = $db->prepare($query);
$stmt->bindParam(':teacher_id', $_SESSION['user_id']);
$stmt->execute();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - Teacher Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="glass-container">
        <div class="glass-card">
            <div class="dashboard-header">
                <div class="user-info">
                    <h2><i class="fas fa-book-open"></i> My Courses</h2>
                    <span class="user-role">Teacher: <?php echo $_SESSION['full_name']; ?></span>
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
                    <i class="fas fa-book"></i>
                    <h3><?php echo count($courses); ?></h3>
                    <p>Total Courses</p>
                </div>
                
                <?php
                $total_students = 0;
                foreach ($courses as $course) {
                    $total_students += $course['student_count'];
                }
                ?>
                
                <div class="stat-card">
                    <i class="fas fa-user-graduate"></i>
                    <h3><?php echo $total_students; ?></h3>
                    <p>Total Students</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-chart-line"></i>
                    <h3><?php echo round($total_students / max(count($courses), 1), 1); ?></h3>
                    <p>Avg. Students/Course</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-star"></i>
                    <h3><?php echo array_sum(array_column($courses, 'credits')); ?></h3>
                    <p>Total Credits</p>
                </div>
            </div>

            <?php if (empty($courses)): ?>
                <div class="alert alert-warning" style="text-align: center; padding: 40px;">
                    <i class="fas fa-book" style="font-size: 3rem; margin-bottom: 20px;"></i>
                    <h3>No Courses Assigned</h3>
                    <p>You haven't been assigned any courses yet.</p>
                    <a href="create_course.php" class="btn btn-primary" style="margin-top: 20px;">
                        <i class="fas fa-plus"></i> Create Your First Course
                    </a>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Course Name</th>
                                <th>Credits</th>
                                <th>Students</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($courses as $course): ?>
                            <tr>
                                <td>
                                    <strong><?php echo $course['course_code']; ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                <td>
                                    <span class="user-role" style="background: rgba(255, 193, 7, 0.3);">
                                        <?php echo $course['credits']; ?> Credits
                                    </span>
                                </td>
                                <td>
                                    <?php if ($course['student_count'] > 0): ?>
                                        <a href="view_students.php?course_id=<?php echo $course['id']; ?>" 
                                           class="btn btn-info btn-sm">
                                            <i class="fas fa-users"></i> <?php echo $course['student_count']; ?> Students
                                        </a>
                                    <?php else: ?>
                                        <span class="user-role" style="background: rgba(108, 117, 125, 0.3);">
                                            No Students
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="manage_grades.php?course_id=<?php echo $course['id']; ?>" 
                                           class="btn btn-success btn-sm">
                                            <i class="fas fa-graduation-cap"></i> Grades
                                        </a>
                                        <a href="schedule_class.php?course_id=<?php echo $course['id']; ?>" 
                                           class="btn btn-primary btn-sm">
                                            <i class="fas fa-calendar"></i> Schedule
                                        </a>
                                        <a href="edit_course.php?id=<?php echo $course['id']; ?>" 
                                           class="btn btn-warning btn-sm">
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
        </div>
    </div>
</body>
</html>