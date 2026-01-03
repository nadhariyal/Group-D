<?php
require_once '../config/Database.php';
require_once '../models/InventoryModel.php';

$database = new Database();
$db = $database->getConnection();
$inventory = new InventoryModel($db);


if (isset($_GET['delete_id'])) {
    $inventory->delete($_GET['delete_id']);
    header("Location: ../views/inventory_dashboard.php");
}

t
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $inventory->create($_POST['name'], $_POST['category'], $_POST['stock'], $_POST['price'], $_POST['supplier']);
    header("Location: ../views/inventory_dashboard.php");
}
?>