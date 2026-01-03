
<?php
session_start();
if ($_SESSION['role'] !== 'Accountant') { header("Location: login.php"); exit(); }

require_once '../controllers/FinanceController.php';
$expenses = $financeModel->getAllExpenses();
$summary = $financeModel->getFinancialSummary();
?>

<h2>Accountant / Auditor Dashboard</h2>

<div style="background: #f4f4f4; padding: 15px; margin-bottom: 20px;">
    <h3>Financial Summary</h3>
    <p>Total Revenue: <b>$<?= number_format($summary['revenue'], 2) ?></b></p>
    <p>Total Expenses: <b>$<?= number_format($summary['expenses'], 2) ?></b></p>
    <p>Net Profit: <b>$<?= number_format($summary['revenue'] - $summary['expenses'], 2) ?></b></p>
</div>

<form method="POST" action="../controllers/FinanceController.php">
    <h3>Record New Expense</h3>
    <input type="text" name="desc" placeholder="Description (e.g., Rent)" required>
    <input type="number" step="0.01" name="amount" placeholder="Amount" required>
    <input type="text" name="category" placeholder="Category">
    <input type="date" name="date" required>
    <button type="submit" name="add_expense">Save Expense</button>
</form>

<hr>

<h3>Expense Logs</h3>
<table border="1">
    <tr>
        <th>Date</th><th>Category</th><th>Description</th><th>Amount</th><th>Action</th>
    </tr>
    <?php foreach ($expenses as $e): ?>
    <tr>
        <td><?= $e['expense_date'] ?></td>
        <td><?= $e['category'] ?></td>
        <td><?= $e['description'] ?></td>
        <td>$<?= number_format($e['amount'], 2) ?></td>
        <td><a href="../controllers/FinanceController.php?delete_id=<?= $e['id'] ?>">Delete</a></td>
    </tr>
    <?php endforeach; ?>
</table>