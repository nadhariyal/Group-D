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


$query = "SELECT * FROM courses WHERE teacher_id = :teacher_id ORDER BY course_name";
$stmt = $db->prepare($query);
$stmt->bindParam(':teacher_id', $_SESSION['user_id']);
$stmt->execute();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['schedule_class'])) {
    $course_id = $_POST['course_id'];
    $class_date = $_POST['class_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $room = $_POST['room'];
    $topic = $_POST['topic'];
    
    
    $create_table = "CREATE TABLE IF NOT EXISTS class_schedule (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_id INT NOT NULL,
        class_date DATE NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        room VARCHAR(50),
        topic TEXT,
        teacher_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (course_id) REFERENCES courses(id),
        FOREIGN KEY (teacher_id) REFERENCES users(id)
    )";
    $db->exec($create_table);
    
    
    $insert_query = "INSERT INTO class_schedule (course_id, class_date, start_time, end_time, room, topic, teacher_id) 
                     VALUES (:course_id, :class_date, :start_time, :end_time, :room, :topic, :teacher_id)";
    $insert_stmt = $db->prepare($insert_query);
    $insert_stmt->bindParam(':course_id', $course_id);
    $insert_stmt->bindParam(':class_date', $class_date);
    $insert_stmt->bindParam(':start_time', $start_time);
    $insert_stmt->bindParam(':end_time', $end_time);
    $insert_stmt->bindParam(':room', $room);
    $insert_stmt->bindParam(':topic', $topic);
    $insert_stmt->bindParam(':teacher_id', $_SESSION['user_id']);
    
    if ($insert_stmt->execute()) {
        $message = "Class scheduled successfully!";
        $message_type = "success";
    } else {
        $message = "Failed to schedule class.";
        $message_type = "danger";
    }
}


$upcoming_query = "SELECT cs.*, c.course_code, c.course_name 
                   FROM class_schedule cs 
                   JOIN courses c ON cs.course_id = c.id 
                   WHERE cs.teacher_id = :teacher_id 
                   AND cs.class_date >= CURDATE() 
                   ORDER BY cs.class_date, cs.start_time 
                   LIMIT 10";
$upcoming_stmt = $db->prepare($upcoming_query);
$upcoming_stmt->bindParam(':teacher_id', $_SESSION['user_id']);
$upcoming_stmt->execute();
$upcoming_classes = $upcoming_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Class - Teacher Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="glass-container">
        <div class="glass-card">
            <div class="dashboard-header">
                <div class="user-info">
                    <h2><i class="fas fa-calendar-plus"></i> Schedule Class</h2>
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

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                
                <div>
                    <h3 style="color: white; margin-bottom: 20px;">
                        <i class="fas fa-plus-circle"></i> Schedule New Class
                    </h3>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="course_id"><i class="fas fa-book"></i> Select Course</label>
                            <select id="course_id" name="course_id" class="form-control" required>
                                <option value="">-- Select Course --</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo $course['id']; ?>">
                                        <?php echo $course['course_code'] . ' - ' . $course['course_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group">
                                <label for="class_date"><i class="fas fa-calendar-day"></i> Date</label>
                                <input type="date" id="class_date" name="class_date" class="form-control" required
                                       min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="room"><i class="fas fa-door-open"></i> Room</label>
                                <input type="text" id="room" name="room" class="form-control" required
                                       placeholder="e.g., Room 101, Lab A">
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group">
                                <label for="start_time"><i class="fas fa-clock"></i> Start Time</label>
                                <input type="time" id="start_time" name="start_time" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="end_time"><i class="fas fa-clock"></i> End Time</label>
                                <input type="time" id="end_time" name="end_time" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="topic"><i class="fas fa-comment-alt"></i> Topic/Agenda</label>
                            <textarea id="topic" name="topic" class="form-control" rows="3"
                                      placeholder="What will be covered in this class?"></textarea>
                        </div>
                        
                        <button type="submit" name="schedule_class" class="btn btn-success btn-block">
                            <i class="fas fa-calendar-check"></i> Schedule Class
                        </button>
                    </form>
                    
                    <div style="margin-top: 30px; background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px;">
                        <h4 style="color: white; margin-bottom: 15px;">
                            <i class="fas fa-bell"></i> Quick Schedule
                        </h4>
                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                            <button type="button" class="btn btn-sm btn-primary" onclick="setTime('09:00', '10:30')">
                                9:00-10:30 AM
                            </button>
                            <button type="button" class="btn btn-sm btn-primary" onclick="setTime('11:00', '12:30')">
                                11:00-12:30 PM
                            </button>
                            <button type="button" class="btn btn-sm btn-primary" onclick="setTime('14:00', '15:30')">
                                2:00-3:30 PM
                            </button>
                            <button type="button" class="btn btn-sm btn-primary" onclick="setTime('16:00', '17:30')">
                                4:00-5:30 PM
                            </button>
                        </div>
                    </div>
                </div>

               
                <div>
                    <h3 style="color: white; margin-bottom: 20px;">
                        <i class="fas fa-calendar-alt"></i> Upcoming Classes
                    </h3>
                    
                    <?php if (empty($upcoming_classes)): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-calendar-times"></i> No upcoming classes scheduled.
                        </div>
                    <?php else: ?>
                        <div style="max-height: 500px; overflow-y: auto;">
                            <?php foreach ($upcoming_classes as $class): 
                                $class_date = new DateTime($class['class_date']);
                                $today = new DateTime();
                                $interval = $today->diff($class_date);
                                $days_diff = $interval->days;
                            ?>
                            <div style="background: rgba(255,255,255,0.1); padding: 15px; margin-bottom: 10px; border-radius: 10px;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <strong style="color: white;"><?php echo $class['course_code']; ?></strong><br>
                                        <span style="color: rgba(255,255,255,0.8); font-size: 0.9rem;">
                                            <?php echo $class['course_name']; ?>
                                        </span>
                                    </div>
                                    <span class="user-role" style="background: <?php 
                                        echo $days_diff == 0 ? 'rgba(220, 53, 69, 0.3)' : 
                                             ($days_diff <= 3 ? 'rgba(255, 193, 7, 0.3)' : 'rgba(40, 167, 69, 0.3)');
                                    ?>;">
                                        <?php 
                                        if ($days_diff == 0) echo 'Today';
                                        elseif ($days_diff == 1) echo 'Tomorrow';
                                        else echo date('M j', strtotime($class['class_date']));
                                        ?>
                                    </span>
                                </div>
                                
                                <div style="margin-top: 10px; color: rgba(255,255,255,0.8); font-size: 0.9rem;">
                                    <div><i class="far fa-clock"></i> 
                                        <?php echo date('g:i A', strtotime($class['start_time'])); ?> - 
                                        <?php echo date('g:i A', strtotime($class['end_time'])); ?>
                                    </div>
                                    <div><i class="fas fa-door-open"></i> <?php echo $class['room']; ?></div>
                                    <?php if ($class['topic']): ?>
                                        <div style="margin-top: 5px;">
                                            <i class="fas fa-comment"></i> 
                                            <em><?php echo htmlspecialchars($class['topic']); ?></em>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div style="margin-top: 20px; text-align: center;">
                            <a href="view_schedule.php" class="btn btn-info">
                                <i class="fas fa-list"></i> View Full Schedule
                            </a>
                            <a href="calendar_view.php" class="btn btn-warning">
                                <i class="fas fa-calendar"></i> Calendar View
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        
        document.getElementById('class_date').value = new Date().toISOString().split('T')[0];
        
    
        const now = new Date();
        now.setHours(now.getHours() + 1);
        const startTime = now.toTimeString().substring(0, 5);
        document.getElementById('start_time').value = startTime;
        
        
        now.setHours(now.getHours() + 1, now.getMinutes() + 30);
        const endTime = now.toTimeString().substring(0, 5);
        document.getElementById('end_time').value = endTime;
        
        
        function setTime(start, end) {
            document.getElementById('start_time').value = start;
            document.getElementById('end_time').value = end;
        }
        
        
        const rooms = ['Room 101', 'Room 102', 'Room 201', 'Room 202', 'Lab A', 'Lab B', 'Auditorium', 'Online'];
        const roomInput = document.getElementById('room');
        roomInput.addEventListener('focus', function() {
            if (!this.value) {
                this.placeholder = 'Suggestions: ' + rooms.join(', ');
            }
        });
    </script>
</body>
</html>