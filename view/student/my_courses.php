<?php
session_start();
require_once '../../config/database.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();


$query = "SELECT c.*, u.full_name as teacher_name 
          FROM courses c 
          LEFT JOIN users u ON c.teacher_id = u.id 
          ORDER BY c.course_code";
$stmt = $db->prepare($query);
$stmt->execute();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);


$enrollment_query = "SELECT course_id FROM enrollments WHERE student_id = :student_id AND status = 'active'";
$enrollment_stmt = $db->prepare($enrollment_query);
$enrollment_stmt->bindParam(':student_id', $_SESSION['user_id']);
$enrollment_stmt->execute();
$enrolled_courses = $enrollment_stmt->fetchAll(PDO::FETCH_COLUMN);


if (isset($_POST['enroll'])) {
    $course_id = $_POST['course_id'];
    
    
    $check_query = "SELECT * FROM enrollments WHERE student_id = :student_id AND course_id = :course_id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':student_id', $_SESSION['user_id']);
    $check_stmt->bindParam(':course_id', $course_id);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() == 0) {
        
        $enroll_query = "INSERT INTO enrollments (student_id, course_id, enrollment_date, status) 
                         VALUES (:student_id, :course_id, CURDATE(), 'active')";
        $enroll_stmt = $db->prepare($enroll_query);
        $enroll_stmt->bindParam(':student_id', $_SESSION['user_id']);
        $enroll_stmt->bindParam(':course_id', $course_id);
        
        if ($enroll_stmt->execute()) {
            $message = "Successfully enrolled in course!";
            $message_type = "success";
            
            $enrollment_stmt->execute();
            $enrolled_courses = $enrollment_stmt->fetchAll(PDO::FETCH_COLUMN);
        } else {
            $message = "Failed to enroll in course.";
            $message_type = "danger";
        }
    } else {
        $message = "You are already enrolled in this course.";
        $message_type = "warning";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - Student Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="glass-container">
        <div class="glass-card">
            <div class="dashboard-header">
                <div class="user-info">
                    <h2><i class="fas fa-book-open"></i> Available Courses</h2>
                    <span class="user-role">Student: <?php echo $_SESSION['full_name']; ?></span>
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

            <?php if (isset($message)): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="stats-grid" style="margin-bottom: 30px;">
                <div class="stat-card">
                    <i class="fas fa-book"></i>
                    <h3><?php echo count($courses); ?></h3>
                    <p>Available Courses</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-user-check"></i>
                    <h3><?php echo count($enrolled_courses); ?></h3>
                    <p>Enrolled Courses</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <h3><?php echo count(array_unique(array_column($courses, 'teacher_id'))); ?></h3>
                    <p>Teachers</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-star"></i>
                    <h3><?php echo array_sum(array_column($courses, 'credits')); ?></h3>
                    <p>Total Credits</p>
                </div>
            </div>

            <h3 style="color: white; margin-bottom: 20px;">
                <i class="fas fa-list"></i> All Courses (<?php echo count($courses); ?>)
            </h3>
            
            <?php if (empty($courses)): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> No courses available at the moment.
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Course Name</th>
                                <th>Teacher</th>
                                <th>Credits</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($courses as $course): 
                                $is_enrolled = in_array($course['id'], $enrolled_courses);
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo $course['course_code']; ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                <td><?php echo htmlspecialchars($course['teacher_name'] ?: 'Not Assigned'); ?></td>
                                <td>
                                    <span class="user-role" style="background: rgba(255, 193, 7, 0.3);">
                                        <?php echo $course['credits']; ?> Credits
                                    </span>
                                </td>
                                <td>
                                    <?php if ($is_enrolled): ?>
                                        <span class="user-role" style="background: rgba(40, 167, 69, 0.3);">
                                            <i class="fas fa-check-circle"></i> Enrolled
                                        </span>
                                    <?php else: ?>
                                        <span class="user-role" style="background: rgba(108, 117, 125, 0.3);">
                                            Available
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($is_enrolled): ?>
                                        <span class="user-role" style="background: rgba(0, 123, 255, 0.3);">
                                            Already Enrolled
                                        </span>
                                    <?php else: ?>
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                            <button type="submit" name="enroll" class="btn btn-success btn-sm">
                                                <i class="fas fa-user-plus"></i> Enroll
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            
            <div style="margin-top: 30px; background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px;">
                <h4 style="color: white; margin-bottom: 15px;">
                    <i class="fas fa-info-circle"></i> Enrollment Information
                </h4>
                <div style="color: rgba(255,255,255,0.8);">
                    <p>• You can enroll in any available course</p>
                    <p>• Each course has a specific credit value</p>
                    <p>• You can view enrolled courses in your dashboard</p>
                    <p>• Contact your advisor if you need to drop a course</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>