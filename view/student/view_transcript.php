<?php
session_start();
require_once '../../config/database.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();


$query = "SELECT g.*, c.course_code, c.course_name, c.credits, u.full_name as teacher_name 
          FROM grades g 
          JOIN courses c ON g.course_id = c.id 
          JOIN users u ON g.teacher_id = u.id 
          WHERE g.student_id = :student_id 
          ORDER BY c.course_code";
$stmt = $db->prepare($query);
$stmt->bindParam(':student_id', $_SESSION['user_id']);
$stmt->execute();
$grades = $stmt->fetchAll(PDO::FETCH_ASSOC);


$total_credits = 0;
$total_points = 0;

foreach ($grades as $grade) {
    $grade_points = getGradePoints($grade['grade']);
    $total_points += ($grade_points * $grade['credits']);
    $total_credits += $grade['credits'];
}

$gpa = ($total_credits > 0) ? round($total_points / $total_credits, 2) : 0.00;


function getGradePoints($grade) {
    $points = [
        'A+' => 4.0, 'A' => 4.0, 'A-' => 3.7,
        'B+' => 3.3, 'B' => 3.0, 'B-' => 2.7,
        'C+' => 2.3, 'C' => 2.0, 'C-' => 1.7,
        'D+' => 1.3, 'D' => 1.0, 'D-' => 0.7,
        'F' => 0.0
    ];
    
    return isset($points[$grade]) ? $points[$grade] : 0.0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Transcript - Student Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="glass-container">
        <div class="glass-card">
            <div class="dashboard-header">
                <div class="user-info">
                    <h2><i class="fas fa-file-invoice"></i> Academic Transcript</h2>
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

            
            <div style="text-align: center; margin-bottom: 30px;">
                <h1 style="color: white; margin-bottom: 10px;">UNIVERSITY MANAGEMENT SYSTEM</h1>
                <h3 style="color: rgba(255,255,255,0.9); margin-bottom: 20px;">OFFICIAL ACADEMIC TRANSCRIPT</h3>
                
                <div style="background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px; display: inline-block; margin: 0 auto;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; text-align: left;">
                        <div>
                            <p style="color: rgba(255,255,255,0.8); margin: 5px 0;">
                                <strong>Student Name:</strong> <?php echo $_SESSION['full_name']; ?>
                            </p>
                            <p style="color: rgba(255,255,255,0.8); margin: 5px 0;">
                                <strong>Student ID:</strong> <?php echo $_SESSION['user_id']; ?>
                            </p>
                        </div>
                        <div>
                            <p style="color: rgba(255,255,255,0.8); margin: 5px 0;">
                                <strong>Date Generated:</strong> <?php echo date('F j, Y'); ?>
                            </p>
                            <p style="color: rgba(255,255,255,0.8); margin: 5px 0;">
                                <strong>Current GPA:</strong> <span style="color: #96c93d; font-weight: bold;"><?php echo $gpa; ?></span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="stats-grid" style="margin-bottom: 30px;">
                <div class="stat-card">
                    <i class="fas fa-book"></i>
                    <h3><?php echo count($grades); ?></h3>
                    <p>Courses Completed</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-star"></i>
                    <h3><?php echo $total_credits; ?></h3>
                    <p>Total Credits</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-chart-line"></i>
                    <h3><?php echo $gpa; ?></h3>
                    <p>GPA</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-graduation-cap"></i>
                    <h3><?php echo calculateLetterGrade($gpa); ?></h3>
                    <p>Grade Average</p>
                </div>
            </div>

            
            <h3 style="color: white; margin-bottom: 20px;">
                <i class="fas fa-list-ol"></i> Course Grades
            </h3>
            
            <?php if (empty($grades)): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> No grades available for your transcript.
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Course Name</th>
                                <th>Credits</th>
                                <th>Teacher</th>
                                <th>Grade</th>
                                <th>Grade Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grades as $grade): 
                                $grade_points = getGradePoints($grade['grade']);
                                $grade_color = getGradeColor($grade['grade']);
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo $grade['course_code']; ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($grade['course_name']); ?></td>
                                <td><?php echo $grade['credits']; ?></td>
                                <td><?php echo htmlspecialchars($grade['teacher_name']); ?></td>
                                <td>
                                    <span class="user-role" style="background: <?php echo $grade_color; ?>;">
                                        <?php echo $grade['grade']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="color: white; font-weight: bold;">
                                        <?php echo $grade_points; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                           
                            <tr style="background: rgba(255,255,255,0.1);">
                                <td colspan="2" style="text-align: right; color: white; font-weight: bold;">TOTAL:</td>
                                <td style="color: white; font-weight: bold;"><?php echo $total_credits; ?></td>
                                <td></td>
                                <td></td>
                                <td style="color: white; font-weight: bold;">GPA: <?php echo $gpa; ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                
                <div style="margin-top: 20px; background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px;">
                    <h4 style="color: white; margin-bottom: 10px;">
                        <i class="fas fa-info-circle"></i> Grade Points System
                    </h4>
                    <div style="display: flex; gap: 20px; flex-wrap: wrap; color: rgba(255,255,255,0.8);">
                        <div>A = 4.0</div>
                        <div>B = 3.0</div>
                        <div>C = 2.0</div>
                        <div>D = 1.0</div>
                        <div>F = 0.0</div>
                    </div>
                </div>
            <?php endif; ?>
            
            
            <div style="margin-top: 40px; text-align: center; color: rgba(255,255,255,0.6); font-size: 0.9rem;">
                <p>This is an unofficial transcript. For official purposes, contact the Registrar's Office.</p>
                <p>© <?php echo date('Y'); ?> University Management System</p>
            </div>
        </div>
    </div>
</body>
</html>

<?php

function getGradeColor($grade) {
    $first_char = substr($grade, 0, 1);
    
    switch ($first_char) {
        case 'A': return 'rgba(40, 167, 69, 0.3)'; 
        case 'B': return 'rgba(0, 123, 255, 0.3)'; 
        case 'C': return 'rgba(255, 193, 7, 0.3)'; 
        case 'D': return 'rgba(220, 53, 69, 0.3)'; 
        case 'F': return 'rgba(108, 117, 125, 0.3)'; 
        default: return 'rgba(255,255,255,0.1)';
    }
}


function calculateLetterGrade($gpa) {
    if ($gpa >= 3.7) return 'A';
    if ($gpa >= 3.3) return 'A-';
    if ($gpa >= 3.0) return 'B+';
    if ($gpa >= 2.7) return 'B';
    if ($gpa >= 2.3) return 'B-';
    if ($gpa >= 2.0) return 'C+';
    if ($gpa >= 1.7) return 'C';
    if ($gpa >= 1.3) return 'C-';
    if ($gpa >= 1.0) return 'D';
    return 'F';
}
?>