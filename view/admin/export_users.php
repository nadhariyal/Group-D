<?php
session_start();
require_once '../../config/database.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();


$query = "SELECT * FROM users ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);


$format = isset($_GET['format']) ? $_GET['format'] : 'csv';

if ($format == 'csv') {
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=users_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    
    
    fputcsv($output, ['ID', 'Username', 'Full Name', 'Email', 'Role', 'Created At']);
    
    
    foreach ($users as $user) {
        fputcsv($output, [
            $user['id'],
            $user['username'],
            $user['full_name'],
            $user['email'],
            ucfirst($user['role']),
            $user['created_at']
        ]);
    }
    
    fclose($output);
    exit;
    
} elseif ($format == 'pdf') {
    
    
    try {
        require_once('../../vendor/autoload.php');
        
        
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        
        $pdf->SetCreator('University Management System');
        $pdf->SetAuthor('Admin');
        $pdf->SetTitle('Users Report');
        $pdf->SetSubject('Users List');
        
        
        $pdf->AddPage();
        
        
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'Users Report - ' . date('F j, Y'), 0, 1, 'C');
        $pdf->Ln(10);
        
        
        $pdf->SetFont('helvetica', '', 10);
        
        $html = '<table border="1" cellpadding="5">
                    <thead>
                        <tr>
                            <th><b>ID</b></th>
                            <th><b>Username</b></th>
                            <th><b>Full Name</b></th>
                            <th><b>Email</b></th>
                            <th><b>Role</b></th>
                            <th><b>Created At</b></th>
                        </tr>
                    </thead>
                    <tbody>';
        
        foreach ($users as $user) {
            $html .= '<tr>
                        <td>' . $user['id'] . '</td>
                        <td>' . $user['username'] . '</td>
                        <td>' . $user['full_name'] . '</td>
                        <td>' . $user['email'] . '</td>
                        <td>' . ucfirst($user['role']) . '</td>
                        <td>' . $user['created_at'] . '</td>
                      </tr>';
        }
        
        $html .= '</tbody></table>';
        
        
        $pdf->writeHTML($html, true, false, true, false, '');
        
        
        $pdf->Output('users_' . date('Y-m-d') . '.pdf', 'D');
        exit;
        
    } catch (Exception $e) {
        
        header("Location: manage_users.php?error=PDF+export+requires+TCPDF+library");
        exit;
    }
} else {
    
    header("Location: manage_users.php");
    exit;
}
?>