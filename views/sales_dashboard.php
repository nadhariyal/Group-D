<?php
session_start();

if ($_SESSION['role'] !== 'Sales Associate') { header("Location: login.php"); exit(); }

require_once '../controllers/SalesController.php';
require_once '../models/InventoryModel.php'; 

$inventory = new InventoryModel($db);
$products = $inventory->readAll();
$sales = $salesModel->getAllSales();
?>

<h2>Sales Associate Dashboard</h2>

<form method="POST" action="../controllers/SalesController.php">
    <h3>Record New Sale</h3>
    <input type="text" name="customer_name" placeholder="Customer Name" required>
    
    <select name="product_id" required>
        <option value="">Select Product</option>
        <?php foreach($products as $p): ?>
            <option value="<?= $p['id'] ?>"><?= $p['product_name'] ?> ($<?= $p['price'] ?>)</option>
        <?php endforeach; ?>
    </select>
    
    <input type="number" name="quantity" placeholder="Quantity" required>
    <input type="hidden" name="unit_price" value="<?= $p['price'] ?>"> <button type="submit" name="add_sale">Submit Sale</button>
</form>

<hr>

<table border="1">
    <tr>
        <th>ID</th><th>Date</th><th>Customer</th><th>Product</th><th>Qty</th><th>Total</th><th>Action</th>
    </tr>
    <?php foreach ($sales as $s): ?>
    <tr>
        <td><?= $s['id'] ?></td>
        <td><?= $s['transaction_date'] ?></td>
        <td><?= $s['customer_name'] ?></td>
        <td><?= $s['product_name'] ?></td>
        <td><?= $s['quantity'] ?></td>
        <td>$<?= $s['total_price'] ?></td>
        <td><a href="../controllers/SalesController.php?delete_id=<?= $s['id'] ?>">Delete</a></td>
    </tr>
    <?php endforeach; ?>
</table>