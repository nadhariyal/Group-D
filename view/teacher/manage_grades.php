<?php
session_start();
require_once '../../config/database.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$message = "";
$message_type = "";


$query = "SELECT * FROM courses WHERE teacher_id = :teacher_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':teacher_id', $_SESSION['user_id']);
$stmt->execute();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);


if (isset($_POST['submit_grade'])) {
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];
    $grade = $_POST['grade'];
    
    
    $check_query = "SELECT * FROM grades WHERE student_id = :student_id AND course_id = :course_id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':student_id', $student_id);
    $check_stmt->bindParam(':course_id', $course_id);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() > 0) {
      
        $update_query = "UPDATE grades SET grade = :grade, teacher_id = :teacher_id 
                         WHERE student_id = :student_id AND course_id = :course_id";
        $update_stmt = $db->prepare($update_query);
        $update_stmt->bindParam(':grade', $grade);
        $update_stmt->bindParam(':teacher_id', $_SESSION['user_id']);
        $update_stmt->bindParam(':student_id', $student_id);
        $update_stmt->bindParam(':course_id', $course_id);
        
        if ($update_stmt->execute()) {
            $message = "Grade updated successfully!";
            $message_type = "success";
        }
    } else {
       
        $insert_query = "INSERT INTO grades (student_id, course_id, grade, teacher_id) 
                         VALUES (:student_id, :course_id, :grade, :teacher_id)";
        $insert_stmt = $db->prepare($insert_query);
        $insert_stmt->bindParam(':student_id', $student_id);
        $insert_stmt->bindParam(':course_id', $course_id);
        $insert_stmt->bindParam(':grade', $grade);
        $insert_stmt->bindParam(':teacher_id', $_SESSION['user_id']);
        
        if ($insert_stmt->execute()) {
            $message = "Grade submitted successfully!";
            $message_type = "success";
        }
    }
}


if (isset($_GET['delete_grade'])) {
    $grade_id = $_GET['delete_grade'];
    
    $delete_query = "DELETE FROM grades WHERE id = :id";
    $delete_stmt = $db->prepare($delete_query);
    $delete_stmt->bindParam(':id', $grade_id);
    
    if ($delete_stmt->execute()) {
        $message = "Grade deleted successfully!";
        $message_type = "success";
    }
}

$students = [];
if (isset($_GET['course_id'])) {
    $course_id = $_GET['course_id'];
    
  
    $course_query = "SELECT * FROM courses WHERE id = :id AND teacher_id = :teacher_id";
    $course_stmt = $db->prepare($course_query);
    $course_stmt->bindParam(':id', $course_id);
    $course_stmt->bindParam(':teacher_id', $_SESSION['user_id']);
    $course_stmt->execute();
    $selected_course = $course_stmt->fetch(PDO::FETCH_ASSOC);
    
   
    $students_query = "SELECT u.id, u.full_name, u.username, g.grade, g.id as grade_id 
                       FROM users u 
                       LEFT JOIN enrollments e ON u.id = e.student_id 
                       LEFT JOIN grades g ON u.id = g.student_id AND g.course_id = :course_id
                       WHERE e.course_id = :course_id AND u.role = 'student' 
                       ORDER BY u.full_name";
    $students_stmt = $db->prepare($students_query);
    $students_stmt->bindParam(':course_id', $course_id);
    $students_stmt->execute();
    $students = $students_stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Grades - Teacher Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="glass-container">
        <div class="glass-card">
            <div class="dashboard-header">
                <div class="user-info">
                    <h2><i class="fas fa-graduation-cap"></i> Manage Grades</h2>
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

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
               
                <div>
                    <h3 style="color: white; margin-bottom: 20px;">
                        <i class="fas fa-book"></i> Select Course
                    </h3>
                    
                    <div class="course-list" style="background: rgba(255,255,255,0.1); border-radius: 10px; padding: 20px;">
                        <?php if (empty($courses)): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> No courses assigned to you.
                            </div>
                        <?php else: ?>
                            <?php foreach ($courses as $course): ?>
                                <a href="manage_grades.php?course_id=<?php echo $course['id']; ?>" 
                                   class="course-item" 
                                   style="display: block; background: rgba(255,255,255,0.15); 
                                          padding: 15px; margin-bottom: 10px; border-radius: 8px;
                                          text-decoration: none; color: white; transition: all 0.3s;">
                                    <strong><?php echo $course['course_code']; ?></strong><br>
                                    <?php echo $course['course_name']; ?>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

               
                <div>
                    <?php if (isset($selected_course)): ?>
                        <h3 style="color: white; margin-bottom: 20px;">
                            <i class="fas fa-users"></i> Students in <?php echo $selected_course['course_code']; ?>
                        </h3>
                        
                        <?php if (empty($students)): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> No students enrolled in this course.
                            </div>
                        <?php else: ?>
                            <div class="table-container">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Student Name</th>
                                            <th>Username</th>
                                            <th>Current Grade</th>
                                            <th>Submit/Update Grade</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($students as $student): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($student['username']); ?></td>
                                            <td>
                                                <?php if ($student['grade']): ?>
                                                    <span class="user-role" style="background: rgba(40, 167, 69, 0.3);">
                                                        <?php echo $student['grade']; ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="user-role" style="background: rgba(255, 193, 7, 0.3);">
                                                        Not Graded
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <form method="POST" action="" style="display: flex; gap: 10px;">
                                                    <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                                                    <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                                                    
                                                    <select name="grade" class="form-control" style="flex: 1; padding: 8px;">
                                                        <option value="">Select Grade</option>
                                                        <option value="A+" <?php echo ($student['grade'] == 'A+') ? 'selected' : ''; ?>>A+</option>
                                                        <option value="A" <?php echo ($student['grade'] == 'A') ? 'selected' : ''; ?>>A</option>
                                                        <option value="A-" <?php echo ($student['grade'] == 'A-') ? 'selected' : ''; ?>>A-</option>
                                                        <option value="B+" <?php echo ($student['grade'] == 'B+') ? 'selected' : ''; ?>>B+</option>
                                                        <option value="B" <?php echo ($student['grade'] == 'B') ? 'selected' : ''; ?>>B</option>
                                                        <option value="B-" <?php echo ($student['grade'] == 'B-') ? 'selected' : ''; ?>>B-</option>
                                                        <option value="C+" <?php echo ($student['grade'] == 'C+') ? 'selected' : ''; ?>>C+</option>
                                                        <option value="C" <?php echo ($student['grade'] == 'C') ? 'selected' : ''; ?>>C</option>
                                                        <option value="D" <?php echo ($student['grade'] == 'D') ? 'selected' : ''; ?>>D</option>
                                                        <option value="F" <?php echo ($student['grade'] == 'F') ? 'selected' : ''; ?>>F</option>
                                                    </select>
                                                    
                                                    <button type="submit" name="submit_grade" class="btn btn-success btn-sm">
                                                        <i class="fas fa-save"></i> Save
                                                    </button>
                                                </form>
                                            </td>
                                            <td>
                                                <?php if ($student['grade_id']): ?>
                                                    <a href="manage_grades.php?delete_grade=<?php echo $student['grade_id']; ?>&course_id=<?php echo $course_id; ?>" 
                                                       class="btn btn-danger btn-sm"
                                                       onclick="return confirm('Delete this grade?')">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div style="margin-top: 20px; background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px;">
                                <h4 style="color: white; margin-bottom: 10px;">
                                    <i class="fas fa-bullhorn"></i> Grade Distribution
                                </h4>
                                <div style="display: flex; justify-content: space-between; color: rgba(255,255,255,0.8);">
                                    <div>Total Students: <?php echo count($students); ?></div>
                                    <div>Graded: <?php echo count(array_filter($students, fn($s) => !empty($s['grade']))); ?></div>
                                    <div>Pending: <?php echo count(array_filter($students, fn($s) => empty($s['grade']))); ?></div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                    <?php else: ?>
                        <div style="text-align: center; padding: 50px;">
                            <i class="fas fa-chalkboard-teacher" style="font-size: 4rem; color: rgba(255,255,255,0.3); margin-bottom: 20px;"></i>
                            <h3 style="color: white;">Select a Course</h3>
                            <p style="color: rgba(255,255,255,0.7);">
                                Please select a course from the left panel to manage grades for that course.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .course-item:hover {
            background: rgba(255,255,255,0.25) !important;
            transform: translateX(5px);
        }
    </style>
</body>
</html>