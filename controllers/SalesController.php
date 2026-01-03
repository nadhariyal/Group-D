<?php
require_once '../config/Database.php';
require_once '../models/SalesModel.php';

$database = new Database();
$db = $database->getConnection();
$salesModel = new SalesModel($db);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_sale'])) {
    $customer = $_POST['customer_name'];
    $product_id = $_POST['product_id'];
    $qty = $_POST['quantity'];
    $price = $_POST['unit_price'];
    $total = $qty * $price;

    if($salesModel->createSale($customer, $product_id, $qty, $total)) {
        header("Location: ../views/sales_dashboard.php?success=1");
    }
}


if (isset($_GET['delete_id'])) {
    $salesModel->deleteSale($_GET['delete_id']);
    header("Location: ../views/sales_dashboard.php");
}
?>