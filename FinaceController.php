<?php
require_once '../config/Database.php';
require_once '../models/FinanceModel.php';

$database = new Database();
$db = $database->getConnection();
$financeModel = new FinanceModel($db);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_expense'])) {
    $financeModel->addExpense($_POST['desc'], $_POST['amount'], $_POST['category'], $_POST['date']);
    header("Location: ../views/finance_dashboard.php");
}

if (isset($_GET['delete_id'])) {
    $financeModel->deleteExpense($_GET['delete_id']);
    header("Location: ../views/finance_dashboard.php");
}
?>