<?php
require_once '../config/Database.php';
require_once '../models/UserModel.php';

$database = new Database();
$db = $database->getConnection();
$userModel = new UserModel($db);


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role_id = $_POST['role_id'];

     
    if ($userModel->register($username, $password, $role_id)) {
        
        header("Location: ../views/login.php?success=1");
        exit();
    } else {
        echo "Registration failed.";
    }
} else {
    
    header("Location: ../views/register.php");
    exit();
}
?>