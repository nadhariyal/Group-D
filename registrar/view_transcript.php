<?php
session_start();
require_once '../../config/database.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'registrar') {
    header("Location: ../../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$student_id = isset($_GET['student_id']) ? $_GET['student_id'] : 0;


$student_query = "SELECT * FROM users WHERE id = :id AND role = 'student'";
$student_stmt = $db->prepare($student_query);
$student_stmt->bindParam(':id', $student_id);
$student_stmt->execute();
$student = $student_stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("Student not found");
}


$grades_query = "SELECT g.*, c.course_code, c.course_name, c.credits 
                 FROM grades g 
                 JOIN courses c ON g.course_id = c.id 
                 WHERE g.student_id = :student_id 
                 ORDER BY c.course_code";
$grades_stmt = $db->prepare($grades_query);
$grades_stmt->bindParam(':student_id', $student_id);
$grades_stmt->execute();
$grades = $grades_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Transcript</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .footer { margin-top: 30px; text-align: center; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>UNIVERSITY MANAGEMENT SYSTEM</h1>
        <h2>OFFICIAL TRANSCRIPT</h2>
    </div>
    
    <div>
        <p><strong>Student Name:</strong> <?php echo htmlspecialchars($student['full_name']); ?></p>
        <p><strong>Student ID:</strong> <?php echo $student['id']; ?></p>
        <p><strong>Date Printed:</strong> <?php echo date('F j, Y'); ?></p>
    </div>
    
    <?php if (empty($grades)): ?>
        <p>No grades available.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Course Code</th>
                    <th>Course Name</th>
                    <th>Credits</th>
                    <th>Grade</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($grades as $grade): ?>
                <tr>
                    <td><?php echo $grade['course_code']; ?></td>
                    <td><?php echo htmlspecialchars($grade['course_name']); ?></td>
                    <td><?php echo $grade['credits']; ?></td>
                    <td><?php echo $grade['grade']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <div class="footer">
        <p>Registrar's Office Signature: _______________________</p>
        <p>Date: <?php echo date('m/d/Y'); ?></p>
        <p>This is an official transcript.</p>
    </div>
    
    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> Print Transcript
        </button>
        <button onclick="window.close()" class="btn btn-danger">
            <i class="fas fa-times"></i> Close
        </button>
    </div>
</body>
</html>