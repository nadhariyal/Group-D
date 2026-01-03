<?php
session_start();
if ($_SESSION['role'] !== 'Inventory Clerk') { header("Location: login.php"); }
require_once '../controllers/InventoryController.php';
$products = $inventory->readAll();
?>

<h2>Inventory Clerk Dashboard</h2>

<form method="POST">
    <input type="text" name="name" placeholder="Product Name" required>
    <input type="number" name="stock" placeholder="Stock Level" required>
    <input type="number" step="0.01" name="price" placeholder="Price" required>
    <button type="submit" name="add_product">Add Product</button>
</form>

<table border="1">
    <tr>
        <th>ID</th><th>Name</th><th>Stock</th><th>Price</th><th>Action</th>
    </tr>
    <?php foreach ($products as $p): ?>
    <tr>
        <td><?= $p['id'] ?></td>
        <td><?= $p['product_name'] ?></td>
        <td><?= $p['stock_level'] ?></td>
        <td><?= $p['price'] ?></td>
        <td>
            <a href="../controllers/InventoryController.php?delete_id=<?= $p['id'] ?>">Delete</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>