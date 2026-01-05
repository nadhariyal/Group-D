<?php
session_start();
require_once '../../config/database.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'registrar') {
    header("Location: ../../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$message = "";
$message_type = "";


if (isset($_POST['update_enrollment'])) {
    $enrollment_id = $_POST['enrollment_id'];
    $status = $_POST['status'];
    
    $update_query = "UPDATE enrollments SET status = :status WHERE id = :id";
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bindParam(':status', $status);
    $update_stmt->bindParam(':id', $enrollment_id);
    
    if ($update_stmt->execute()) {
        $message = "Enrollment updated successfully!";
        $message_type = "success";
    } else {
        $message = "Failed to update enrollment.";
        $message_type = "danger";
    }
}


if (isset($_GET['delete'])) {
    $enrollment_id = $_GET['delete'];
    
    $delete_query = "DELETE FROM enrollments WHERE id = :id";
    $delete_stmt = $db->prepare($delete_query);
    $delete_stmt->bindParam(':id', $enrollment_id);
    
    if ($delete_stmt->execute()) {
        $message = "Enrollment removed successfully!";
        $message_type = "success";
    } else {
        $message = "Failed to remove enrollment.";
        $message_type = "danger";
    }
}


if (isset($_POST['add_enrollment'])) {
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];
    
    $insert_query = "INSERT INTO enrollments (student_id, course_id, enrollment_date, status) 
                     VALUES (:student_id, :course_id, CURDATE(), 'active')";
    $insert_stmt = $db->prepare($insert_query);
    $insert_stmt->bindParam(':student_id', $student_id);
    $insert_stmt->bindParam(':course_id', $course_id);
    
    if ($insert_stmt->execute()) {
        $message = "Student enrolled successfully!";
        $message_type = "success";
    } else {
        $message = "Failed to enroll student.";
        $message_type = "danger";
    }
}


$query = "SELECT e.*, u.full_name as student_name, c.course_name 
          FROM enrollments e 
          JOIN users u ON e.student_id = u.id 
          JOIN courses c ON e.course_id = c.id 
          ORDER BY e.enrollment_date DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);


$students_query = "SELECT id, full_name FROM users WHERE role = 'student' ORDER BY full_name";
$students_stmt = $db->prepare($students_query);
$students_stmt->execute();
$students = $students_stmt->fetchAll(PDO::FETCH_ASSOC);


$courses_query = "SELECT id, course_code, course_name FROM courses ORDER BY course_code";
$courses_stmt = $db->prepare($courses_query);
$courses_stmt->execute();
$courses = $courses_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Enrollments - Registrar Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="glass-container">
        <div class="glass-card">
            <div class="dashboard-header">
                <div class="user-info">
                    <h2><i class="fas fa-clipboard-list"></i> Manage Enrollments</h2>
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

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            
            <h3 style="color: white; margin-bottom: 20px;">
                <i class="fas fa-plus-circle"></i> Add New Enrollment
            </h3>
            
            <form method="POST" action="" style="margin-bottom: 30px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label for="student_id"><i class="fas fa-user-graduate"></i> Student</label>
                        <select id="student_id" name="student_id" class="form-control" required>
                            <option value="">-- Select Student --</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?php echo $student['id']; ?>">
                                    <?php echo htmlspecialchars($student['full_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="course_id"><i class="fas fa-book"></i> Course</label>
                        <select id="course_id" name="course_id" class="form-control" required>
                            <option value="">-- Select Course --</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo $course['id']; ?>">
                                    <?php echo $course['course_code'] . ' - ' . $course['course_name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <button type="submit" name="add_enrollment" class="btn btn-success">
                    <i class="fas fa-user-plus"></i> Enroll Student
                </button>
            </form>

            
            <h3 style="color: white; margin-bottom: 20px;">
                <i class="fas fa-list"></i> All Enrollments (<?php echo count($enrollments); ?>)
            </h3>
            
            <?php if (empty($enrollments)): ?>
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
                            <?php foreach ($enrollments as $enrollment): ?>
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
                                        
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="enrollment_id" value="<?php echo $enrollment['id']; ?>">
                                            <select name="status" style="padding: 5px; margin-right: 10px;">
                                                <option value="active" <?php echo ($enrollment['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                                <option value="completed" <?php echo ($enrollment['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                                                <option value="dropped" <?php echo ($enrollment['status'] == 'dropped') ? 'selected' : ''; ?>>Dropped</option>
                                            </select>
                                            <button type="submit" name="update_enrollment" class="btn btn-success btn-sm">
                                                <i class="fas fa-save"></i> Update
                                            </button>
                                        </form>
                                        
                                        
                                        <a href="manage_enrollments.php?delete=<?php echo $enrollment['id']; ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Are you sure you want to remove this enrollment?')">
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
        </div>
    </div>
</body>
</html>