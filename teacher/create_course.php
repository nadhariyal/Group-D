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


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_course'])) {
    $course_code = $_POST['course_code'];
    $course_name = $_POST['course_name'];
    $credits = $_POST['credits'];
    
    
    if (empty($course_code) || empty($course_name) || empty($credits)) {
        $message = "Please fill in all required fields!";
        $message_type = "danger";
    } else {

        $check_query = "SELECT * FROM courses WHERE course_code = :course_code";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':course_code', $course_code);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() > 0) {
            $message = "Course code already exists! Please choose a different code.";
            $message_type = "danger";
        } else {
            
            $insert_query = "INSERT INTO courses (course_code, course_name, teacher_id, credits) 
                             VALUES (:course_code, :course_name, :teacher_id, :credits)";
            $insert_stmt = $db->prepare($insert_query);
            $insert_stmt->bindParam(':course_code', $course_code);
            $insert_stmt->bindParam(':course_name', $course_name);
            $insert_stmt->bindParam(':teacher_id', $_SESSION['user_id']);
            $insert_stmt->bindParam(':credits', $credits);
            
            if ($insert_stmt->execute()) {
                $message = "Course created successfully!";
                $message_type = "success";
                
                
                $_POST = array();
            } else {
                $message = "Failed to create course. Please try again.";
                $message_type = "danger";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Course - Teacher Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="glass-container">
        <div class="glass-card">
            <div class="dashboard-header">
                <div class="user-info">
                    <h2><i class="fas fa-plus-circle"></i> Create New Course</h2>
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

            <div style="max-width: 600px; margin: 0 auto;">
                <h3 style="color: white; margin-bottom: 30px; text-align: center;">
                    <i class="fas fa-book-medical"></i> Create a New Course
                </h3>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="course_code"><i class="fas fa-code"></i> Course Code *</label>
                        <input type="text" id="course_code" name="course_code" class="form-control" required
                               value="<?php echo isset($_POST['course_code']) ? htmlspecialchars($_POST['course_code']) : ''; ?>"
                               placeholder="e.g., CS101, MATH201">
                    </div>
                    
                    <div class="form-group">
                        <label for="course_name"><i class="fas fa-book"></i> Course Name *</label>
                        <input type="text" id="course_name" name="course_name" class="form-control" required
                               value="<?php echo isset($_POST['course_name']) ? htmlspecialchars($_POST['course_name']) : ''; ?>"
                               placeholder="e.g., Introduction to Programming">
                    </div>
                    
                    <div class="form-group">
                        <label for="credits"><i class="fas fa-star"></i> Credits *</label>
                        <select id="credits" name="credits" class="form-control" required>
                            <option value="">Select Credits</option>
                            <option value="1" <?php echo (isset($_POST['credits']) && $_POST['credits'] == '1') ? 'selected' : ''; ?>>1 Credit</option>
                            <option value="2" <?php echo (isset($_POST['credits']) && $_POST['credits'] == '2') ? 'selected' : ''; ?>>2 Credits</option>
                            <option value="3" <?php echo (isset($_POST['credits']) && $_POST['credits'] == '3') ? 'selected' : ''; ?>>3 Credits</option>
                            <option value="4" <?php echo (isset($_POST['credits']) && $_POST['credits'] == '4') ? 'selected' : ''; ?>>4 Credits</option>
                        </select>
                    </div>
                    
                    <div style="background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px; margin: 20px 0;">
                        <h4 style="color: white; margin-bottom: 15px;">
                            <i class="fas fa-info-circle"></i> Course Information
                        </h4>
                        <p style="color: rgba(255,255,255,0.8);">
                            <strong>Instructor:</strong> <?php echo $_SESSION['full_name']; ?><br>
                            <strong>Created:</strong> <?php echo date('F j, Y'); ?><br>
                            <strong>Status:</strong> <span style="color: #96c93d;">Active</span>
                        </p>
                    </div>
                    
                    <div style="display: flex; gap: 15px; margin-top: 30px;">
                        <button type="submit" name="create_course" class="btn btn-success" style="flex: 1;">
                            <i class="fas fa-plus-circle"></i> Create Course
                        </button>
                        <button type="reset" class="btn btn-warning">
                            <i class="fas fa-redo"></i> Clear Form
                        </button>
                        <a href="my_courses.php" class="btn btn-info">
                            <i class="fas fa-list"></i> My Courses
                        </a>
                    </div>
                </form>
                
                <div style="margin-top: 40px; background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px;">
                    <h4 style="color: white; margin-bottom: 15px;">
                        <i class="fas fa-question-circle"></i> Quick Tips
                    </h4>
                    <div style="color: rgba(255,255,255,0.8);">
                        <p>• Use a unique course code (e.g., CS101)</p>
                        <p>• Course name should be descriptive</p>
                        <p>• Most courses are 3 credits</p>
                        <p>• Course will be available to students immediately</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        
        document.getElementById('course_code').addEventListener('blur', function() {
            const courseCode = this.value.toUpperCase();
            const courseNameField = document.getElementById('course_name');
            
            if (!courseNameField.value && courseCode) {
                const suggestions = {
                    'CS101': 'Introduction to Computer Science',
                    'MATH101': 'Calculus I',
                    'ENG101': 'English Composition',
                    'PHY101': 'Physics I'
                };
                
                if (suggestions[courseCode]) {
                    courseNameField.value = suggestions[courseCode];
                }
            }
        });
        
        
        document.getElementById('credits').value = '3';
    </script>
</body>
</html>