<?php
session_start();
require_once '../../config/database.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();


$query = "SELECT c.id, c.course_code, c.course_name 
          FROM enrollments e 
          JOIN courses c ON e.course_id = c.id 
          WHERE e.student_id = :student_id AND e.status = 'active'";
$stmt = $db->prepare($query);
$stmt->bindParam(':student_id', $_SESSION['user_id']);
$stmt->execute();
$enrolled_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);


$schedule = [];
if (!empty($enrolled_courses)) {
    $course_ids = array_column($enrolled_courses, 'id');
    $placeholders = implode(',', array_fill(0, count($course_ids), '?'));
    
    $schedule_query = "SELECT cs.*, c.course_code, c.course_name 
                       FROM class_schedule cs 
                       JOIN courses c ON cs.course_id = c.id 
                       WHERE cs.course_id IN ($placeholders) 
                       AND cs.class_date >= CURDATE() 
                       ORDER BY cs.class_date, cs.start_time";
    $schedule_stmt = $db->prepare($schedule_query);
    $schedule_stmt->execute($course_ids);
    $schedule = $schedule_stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Schedule - Student Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="glass-container">
        <div class="glass-card">
            <div class="dashboard-header">
                <div class="user-info">
                    <h2><i class="fas fa-calendar-alt"></i> My Class Schedule</h2>
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

            <div class="stats-grid" style="margin-bottom: 30px;">
                <div class="stat-card">
                    <i class="fas fa-book"></i>
                    <h3><?php echo count($enrolled_courses); ?></h3>
                    <p>Enrolled Courses</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-calendar-day"></i>
                    <h3><?php echo count($schedule); ?></h3>
                    <p>Upcoming Classes</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-clock"></i>
                    <h3><?php echo count(array_unique(array_column($schedule, 'class_date'))); ?></h3>
                    <p>Class Days</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-door-open"></i>
                    <h3><?php echo count(array_unique(array_column($schedule, 'room'))); ?></h3>
                    <p>Different Rooms</p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
                <!-- Enrolled Courses -->
                <div>
                    <h3 style="color: white; margin-bottom: 20px;">
                        <i class="fas fa-bookmark"></i> My Courses
                    </h3>
                    
                    <?php if (empty($enrolled_courses)): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> You are not enrolled in any courses.
                        </div>
                    <?php else: ?>
                        <div style="max-height: 400px; overflow-y: auto;">
                            <?php foreach ($enrolled_courses as $course): ?>
                            <div style="background: rgba(255,255,255,0.1); padding: 15px; margin-bottom: 10px; border-radius: 10px;">
                                <strong style="color: white;"><?php echo $course['course_code']; ?></strong><br>
                                <span style="color: rgba(255,255,255,0.8); font-size: 0.9rem;">
                                    <?php echo $course['course_name']; ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                
                <div>
                    <h3 style="color: white; margin-bottom: 20px;">
                        <i class="fas fa-calendar"></i> Upcoming Classes
                    </h3>
                    
                    <?php if (empty($schedule)): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-calendar-times"></i> No upcoming classes scheduled.
                        </div>
                    <?php else: ?>
                        <div style="max-height: 500px; overflow-y: auto;">
                            <?php 
                            $grouped_schedule = [];
                            foreach ($schedule as $class) {
                                $date = $class['class_date'];
                                $grouped_schedule[$date][] = $class;
                            }
                            
                            foreach ($grouped_schedule as $date => $classes): 
                                $date_obj = new DateTime($date);
                                $today = new DateTime();
                                $interval = $today->diff($date_obj);
                                $days_diff = $interval->days;
                            ?>
                            <div style="background: rgba(255,255,255,0.1); padding: 15px; margin-bottom: 20px; border-radius: 10px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                    <h4 style="color: white; margin: 0;">
                                        <?php echo date('l, F j, Y', strtotime($date)); ?>
                                    </h4>
                                    <span class="user-role" style="background: <?php 
                                        echo $days_diff == 0 ? 'rgba(220, 53, 69, 0.3)' : 
                                             ($days_diff == 1 ? 'rgba(255, 193, 7, 0.3)' : 'rgba(40, 167, 69, 0.3)');
                                    ?>;">
                                        <?php 
                                        if ($days_diff == 0) echo 'Today';
                                        elseif ($days_diff == 1) echo 'Tomorrow';
                                        else echo $days_diff . ' days';
                                        ?>
                                    </span>
                                </div>
                                
                                <?php foreach ($classes as $class): ?>
                                <div style="background: rgba(255,255,255,0.05); padding: 10px; margin-bottom: 10px; border-radius: 8px;">
                                    <div style="display: flex; justify-content: space-between;">
                                        <div>
                                            <strong style="color: white;"><?php echo $class['course_code']; ?></strong><br>
                                            <span style="color: rgba(255,255,255,0.8); font-size: 0.9rem;">
                                                <?php echo $class['course_name']; ?>
                                            </span>
                                        </div>
                                        <div style="text-align: right;">
                                            <span style="color: white;">
                                                <?php echo date('g:i A', strtotime($class['start_time'])); ?> - 
                                                <?php echo date('g:i A', strtotime($class['end_time'])); ?>
                                            </span><br>
                                            <span style="color: rgba(255,255,255,0.7); font-size: 0.9rem;">
                                                <i class="fas fa-door-open"></i> <?php echo $class['room']; ?>
                                            </span>
                                        </div>
                                    </div>
                                    <?php if ($class['topic']): ?>
                                    <div style="margin-top: 5px; color: rgba(255,255,255,0.7); font-size: 0.9rem;">
                                        <i class="fas fa-comment"></i> <?php echo htmlspecialchars($class['topic']); ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
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