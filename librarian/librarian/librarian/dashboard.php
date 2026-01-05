<?php
session_start();
require_once '../../config/database.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'librarian') {
    header("Location: ../../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();


$query = "SELECT COUNT(*) as total_books FROM books";
$stmt = $db->prepare($query);
$stmt->execute();
$total_books = $stmt->fetch(PDO::FETCH_ASSOC)['total_books'];

$query = "SELECT SUM(quantity) as total_copies FROM books";
$stmt = $db->prepare($query);
$stmt->execute();
$total_copies = $stmt->fetch(PDO::FETCH_ASSOC)['total_copies'];

$query = "SELECT SUM(available) as available_copies FROM books";
$stmt = $db->prepare($query);
$stmt->execute();
$available_copies = $stmt->fetch(PDO::FETCH_ASSOC)['available_copies'];


$query = "SELECT * FROM books ORDER BY id DESC LIMIT 10";
$stmt = $db->prepare($query);
$stmt->execute();
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Librarian Dashboard - University System</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="glass-container">
        <div class="glass-card">
            <div class="dashboard-header">
                <div class="user-info">
                    <h2>Welcome, <?php echo $_SESSION['full_name']; ?>!</h2>
                    <span class="user-role">Librarian</span>
                </div>
                <a href="../../index.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-book"></i>
                    <h3><?php echo $total_books; ?></h3>
                    <p>Total Books</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-copy"></i>
                    <h3><?php echo $total_copies; ?></h3>
                    <p>Total Copies</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-check-circle"></i>
                    <h3><?php echo $available_copies; ?></h3>
                    <p>Available Copies</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <h3>45</h3>
                    <p>Active Borrowers</p>
                </div>
            </div>
            
            <h3 style="color: white; margin-bottom: 20px;">Recent Books</h3>
            
            <?php if (empty($books)): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> No books in library.
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ISBN</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Quantity</th>
                                <th>Available</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($books as $book): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                                <td><?php echo htmlspecialchars($book['title']); ?></td>
                                <td><?php echo htmlspecialchars($book['author']); ?></td>
                                <td><?php echo $book['quantity']; ?></td>
                                <td>
                                    <span class="user-role" style="background: <?php 
                                        echo $book['available'] > 0 ? 'rgba(40, 167, 69, 0.3)' : 'rgba(220, 53, 69, 0.3)';
                                    ?>;">
                                        <?php echo $book['available']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="#" class="btn btn-success btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="#" class="btn btn-primary btn-sm">
                                            <i class="fas fa-exchange-alt"></i> Borrow
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            
            <div style="margin-top: 30px; display: flex; gap: 15px;">
                <a href="manage_books.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Book
                </a>
                <a href="#" class="btn btn-success">
                    <i class="fas fa-search"></i> Search Books
                </a>
                <a href="#" class="btn btn-warning">
                    <i class="fas fa-history"></i> View Borrow History
                </a>
            </div>
        </div>
    </div>
</body>
</html>