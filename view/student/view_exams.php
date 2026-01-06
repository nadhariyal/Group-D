<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();


$query = "SELECT e.*, c.course_code, c.course_name 
          FROM exams e 
          JOIN courses c ON e.course_id = c.id 
          JOIN enrollments en ON e.course_id = en.course_id 
          WHERE en.student_id = :student_id 
          AND en.status = 'active' 
          AND e.exam_date >= CURDATE() 
          ORDER BY e.exam_date, e.start_time";
$stmt = $db->prepare($query);
$stmt->bindParam(':student_id', $_SESSION['user_id']);
$stmt->execute();
$exams = $stmt->fetchAll(PDO::FETCH_ASSOC);


$past_query = "SELECT e.*, c.course_code, c.course_name 
               FROM exams e 
               JOIN courses c ON e.course_id = c.id 
               JOIN enrollments en ON e.course_id = en.course_id 
               WHERE en.student_id = :student_id 
               AND en.status = 'active' 
               AND e.exam_date < CURDATE() 
               ORDER BY e.exam_date DESC 
               LIMIT 10";
$past_stmt = $db->prepare($past_query);
$past_stmt->bindParam(':student_id', $_SESSION['user_id']);
$past_stmt->execute();
$past_exams = $past_stmt->fetchAll(PDO::FETCH_ASSOC);


$upcoming_count = count($exams);
$past_count = count($past_exams);
$total_count = $upcoming_count + $past_count;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Exams - Student Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="glass-container">
        <div class="glass-card">
            <div class="dashboard-header">
                <div class="user-info">
                    <h2><i class="fas fa-calendar-alt"></i> My Exams</h2>
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
                    <i class="fas fa-clipboard-list"></i>
                    <h3><?php echo $total_count; ?></h3>
                    <p>Total Exams</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-calendar-day"></i>
                    <h3><?php echo $upcoming_count; ?></h3>
                    <p>Upcoming</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-history"></i>
                    <h3><?php echo $past_count; ?></h3>
                    <p>Past Exams</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-book"></i>
                    <h3><?php echo count(array_unique(array_column($exams, 'course_id'))); ?></h3>
                    <p>Courses</p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                
                <div>
                    <h3 style="color: white; margin-bottom: 20px;">
                        <i class="fas fa-calendar-plus"></i> Upcoming Exams (<?php echo $upcoming_count; ?>)
                    </h3>
                    
                    <?php if (empty($exams)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> No upcoming exams!
                        </div>
                    <?php else: ?>
                        <div style="max-height: 400px; overflow-y: auto;">
                            <?php foreach ($exams as $exam): 
                                $days_left = floor((strtotime($exam['exam_date']) - time()) / (60 * 60 * 24));
                                $status_color = '';
                                if ($days_left == 0) {
                                    $status_color = 'rgba(220, 53, 69, 0.3)'; // Red - Today
                                    $status_text = 'TODAY';
                                } elseif ($days_left <= 3) {
                                    $status_color = 'rgba(255, 193, 7, 0.3)'; // Yellow - Soon
                                    $status_text = $days_left . ' days';
                                } else {
                                    $status_color = 'rgba(40, 167, 69, 0.3)'; // Green - Later
                                    $status_text = $days_left . ' days';
                                }
                            ?>
                            <div style="background: rgba(255,255,255,0.1); padding: 15px; margin-bottom: 10px; border-radius: 10px;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <strong style="color: white;"><?php echo htmlspecialchars($exam['exam_name']); ?></strong><br>
                                        <span style="color: rgba(255,255,255,0.8); font-size: 0.9rem;">
                                            <?php echo $exam['course_code']; ?> - <?php echo $exam['course_name']; ?>
                                        </span>
                                    </div>
                                    <span class="user-role" style="background: <?php echo $status_color; ?>;">
                                        <?php echo $status_text; ?>
                                    </span>
                                </div>
                                
                                <div style="margin-top: 10px; color: rgba(255,255,255,0.7); font-size: 0.9rem;">
                                    <div><i class="far fa-calendar"></i> 
                                        <?php echo date('l, F j, Y', strtotime($exam['exam_date'])); ?>
                                    </div>
                                    <?php if ($exam['start_time']): ?>
                                    <div><i class="far fa-clock"></i> 
                                        <?php echo date('g:i A', strtotime($exam['start_time'])); ?> - 
                                        <?php echo date('g:i A', strtotime($exam['end_time'])); ?>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($exam['room']): ?>
                                    <div><i class="fas fa-door-open"></i> <?php echo $exam['room']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                
                <div>
                    <h3 style="color: white; margin-bottom: 20px;">
                        <i class="fas fa-history"></i> Recent Past Exams (<?php echo $past_count; ?>)
                    </h3>
                    
                    <?php if (empty($past_exams)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No past exams found.
                        </div>
                    <?php else: ?>
                        <div style="max-height: 400px; overflow-y: auto;">
                            <?php foreach ($past_exams as $exam): 
                                $days_ago = floor((time() - strtotime($exam['exam_date'])) / (60 * 60 * 24));
                            ?>
                            <div style="background: rgba(255,255,255,0.05); padding: 15px; margin-bottom: 10px; border-radius: 10px;">
                                <div style="display: flex; justify-content: space-between;">
                                    <div>
                                        <strong style="color: white;"><?php echo htmlspecialchars($exam['exam_name']); ?></strong><br>
                                        <span style="color: rgba(255,255,255,0.7); font-size: 0.9rem;">
                                            <?php echo $exam['course_code']; ?>
                                        </span>
                                    </div>
                                    <div style="text-align: right;">
                                        <span style="color: rgba(255,255,255,0.6); font-size: 0.9rem;">
                                            <?php echo date('M j, Y', strtotime($exam['exam_date'])); ?>
                                        </span><br>
                                        <span style="color: rgba(255,255,255,0.5); font-size: 0.8rem;">
                                            <?php echo $days_ago; ?> days ago
                                        </span>
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