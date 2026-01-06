<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$message = "";
$message_type = "";


$query = "SELECT c.* FROM enrollments e 
          JOIN courses c ON e.course_id = c.id 
          WHERE e.student_id = :student_id AND e.status = 'active'";
$stmt = $db->prepare($query);
$stmt->bindParam(':student_id', $_SESSION['user_id']);
$stmt->execute();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);


if (isset($_POST['log_study'])) {
    $course_id = $_POST['course_id'];
    $hours = $_POST['hours'];
    $topic = $_POST['topic'];
    $study_date = $_POST['study_date'];
    
    $insert_query = "INSERT INTO study_sessions (student_id, course_id, study_date, hours, topic) 
                     VALUES (:student_id, :course_id, :study_date, :hours, :topic)";
    $insert_stmt = $db->prepare($insert_query);
    $insert_stmt->bindParam(':student_id', $_SESSION['user_id']);
    $insert_stmt->bindParam(':course_id', $course_id);
    $insert_stmt->bindParam(':study_date', $study_date);
    $insert_stmt->bindParam(':hours', $hours);
    $insert_stmt->bindParam(':topic', $topic);
    
    if ($insert_stmt->execute()) {
        $message = "Study session logged successfully! Total hours added: " . $hours;
        $message_type = "success";
    } else {
        $message = "Failed to log study session.";
        $message_type = "danger";
    }
}

$history_query = "SELECT ss.*, c.course_code, c.course_name 
                  FROM study_sessions ss 
                  JOIN courses c ON ss.course_id = c.id 
                  WHERE ss.student_id = :student_id 
                  ORDER BY ss.study_date DESC 
                  LIMIT 10";
$history_stmt = $db->prepare($history_query);
$history_stmt->bindParam(':student_id', $_SESSION['user_id']);
$history_stmt->execute();
$study_history = $history_stmt->fetchAll(PDO::FETCH_ASSOC);


$total_query = "SELECT SUM(hours) as total_hours FROM study_sessions WHERE student_id = :student_id";
$total_stmt = $db->prepare($total_query);
$total_stmt->bindParam(':student_id', $_SESSION['user_id']);
$total_stmt->execute();
$total_hours = $total_stmt->fetch(PDO::FETCH_ASSOC)['total_hours'] ?: 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Study Hours - Student Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="glass-container">
        <div class="glass-card">
            <div class="dashboard-header">
                <div class="user-info">
                    <h2><i class="fas fa-clock"></i> Log Study Hours</h2>
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

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                
                <div>
                    <h3 style="color: white; margin-bottom: 20px;">
                        <i class="fas fa-plus-circle"></i> Log New Study Session
                    </h3>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label><i class="fas fa-book"></i> Select Course</label>
                            <select name="course_id" class="form-control" required>
                                <option value="">-- Select Course --</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo $course['id']; ?>">
                                        <?php echo $course['course_code'] . ' - ' . $course['course_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-calendar-day"></i> Study Date</label>
                            <input type="date" name="study_date" class="form-control" required 
                                   value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-hourglass-half"></i> Hours Studied</label>
                            <input type="number" name="hours" class="form-control" required 
                                   min="0.5" max="12" step="0.5" placeholder="e.g., 2.5">
                            <small style="color: rgba(255,255,255,0.6);">Enter hours (0.5 = 30 minutes)</small>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-comment-alt"></i> Topic/Subject (Optional)</label>
                            <input type="text" name="topic" class="form-control" 
                                   placeholder="What did you study?">
                        </div>
                        
                        <button type="submit" name="log_study" class="btn btn-success btn-block">
                            <i class="fas fa-save"></i> Log Study Session
                        </button>
                    </form>
                </div>

                
                <div>
                    <h3 style="color: white; margin-bottom: 20px;">
                        <i class="fas fa-chart-bar"></i> Study Statistics
                    </h3>
                    
                    <div style="background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                        <h4 style="color: white; margin-bottom: 15px;">
                            <i class="fas fa-tachometer-alt"></i> Quick Stats
                        </h4>
                        <p style="color: rgba(255,255,255,0.8);">
                            <strong>Total Study Hours:</strong> <?php echo round($total_hours, 1); ?> hours<br>
                            <strong>Total Sessions:</strong> <?php echo count($study_history); ?><br>
                            <strong>Average per Session:</strong> 
                            <?php 
                                if (count($study_history) > 0) {
                                    echo round($total_hours / count($study_history), 1) . ' hours';
                                } else {
                                    echo '0 hours';
                                }
                            ?>
                        </p>
                    </div>
                    
                    <h4 style="color: white; margin-bottom: 15px;">
                        <i class="fas fa-history"></i> Recent Study Sessions
                    </h4>
                    
                    <?php if (empty($study_history)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No study sessions logged yet.
                        </div>
                    <?php else: ?>
                        <div style="max-height: 300px; overflow-y: auto;">
                            <?php foreach ($study_history as $session): ?>
                            <div style="background: rgba(255,255,255,0.05); padding: 10px; margin-bottom: 10px; border-radius: 8px;">
                                <div style="display: flex; justify-content: space-between;">
                                    <div>
                                        <strong style="color: white;"><?php echo $session['course_code']; ?></strong><br>
                                        <span style="color: rgba(255,255,255,0.7); font-size: 0.9rem;">
                                            <?php echo date('M j, Y', strtotime($session['study_date'])); ?>
                                        </span>
                                    </div>
                                    <div style="text-align: right;">
                                        <span style="color: #96c93d; font-weight: bold;">
                                            <?php echo $session['hours']; ?> hours
                                        </span><br>
                                        <?php if ($session['topic']): ?>
                                            <span style="color: rgba(255,255,255,0.6); font-size: 0.9rem;">
                                                <?php echo htmlspecialchars($session['topic']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>