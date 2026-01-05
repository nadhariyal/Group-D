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


$course_id = isset($_GET['id']) ? $_GET['id'] : 0;


$query = "SELECT * FROM courses WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $course_id);
$stmt->execute();

if ($stmt->rowCount() == 0) {
    header("Location: my_courses.php");
    exit();
}

$course = $stmt->fetch(PDO::FETCH_ASSOC);


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_course'])) {
    $course_code = $_POST['course_code'];
    $course_name = $_POST['course_name'];
    $credits = $_POST['credits'];
    
    $update_query = "UPDATE courses SET course_code = :course_code, course_name = :course_name, credits = :credits 
                     WHERE id = :id";
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bindParam(':course_code', $course_code);
    $update_stmt->bindParam(':course_name', $course_name);
    $update_stmt->bindParam(':credits', $credits);
    $update_stmt->bindParam(':id', $course_id);
    
    if ($update_stmt->execute()) {
        $message = "Course updated successfully!";
        $message_type = "success";
    } else {
        $message = "Failed to update course.";
        $message_type = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course - Teacher Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="glass-container">
        <div class="glass-card">
            <div class="dashboard-header">
                <div class="user-info">
                    <h2><i class="fas fa-edit"></i> Edit Course</h2>
                    <span class="user-role">Teacher</span>
                </div>
                <div>
                    <a href="my_courses.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Back
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

            <div style="max-width: 500px; margin: 0 auto;">
                <h3 style="color: white; margin-bottom: 20px; text-align: center;">
                    Edit Course Details
                </h3>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="course_code">Course Code</label>
                        <input type="text" id="course_code" name="course_code" class="form-control" required
                               value="<?php echo htmlspecialchars($course['course_code']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="course_name">Course Name</label>
                        <input type="text" id="course_name" name="course_name" class="form-control" required
                               value="<?php echo htmlspecialchars($course['course_name']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="credits">Credits</label>
                        <select id="credits" name="credits" class="form-control" required>
                            <option value="1" <?php echo ($course['credits'] == 1) ? 'selected' : ''; ?>>1 Credit</option>
                            <option value="2" <?php echo ($course['credits'] == 2) ? 'selected' : ''; ?>>2 Credits</option>
                            <option value="3" <?php echo ($course['credits'] == 3) ? 'selected' : ''; ?>>3 Credits</option>
                            <option value="4" <?php echo ($course['credits'] == 4) ? 'selected' : ''; ?>>4 Credits</option>
                        </select>
                    </div>
                    
                    <button type="submit" name="update_course" class="btn btn-success btn-block">
                        <i class="fas fa-save"></i> Update Course
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>