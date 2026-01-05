<?php
session_start();
require_once '../../config/database.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'librarian') {
    header("Location: ../../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();


$create_table = "CREATE TABLE IF NOT EXISTS borrowings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    user_id INT NOT NULL,
    borrow_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE,
    FOREIGN KEY (book_id) REFERENCES books(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
)";
$db->exec($create_table);


if (isset($_GET['return'])) {
    $borrow_id = $_GET['return'];
    
   
    $get_query = "SELECT book_id FROM borrowings WHERE id = :id";
    $get_stmt = $db->prepare($get_query);
    $get_stmt->bindParam(':id', $borrow_id);
    $get_stmt->execute();
    $borrow = $get_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($borrow) {
        
        $update_query = "UPDATE borrowings SET return_date = CURDATE() WHERE id = :id";
        $update_stmt = $db->prepare($update_query);
        $update_stmt->bindParam(':id', $borrow_id);
        
        
        $book_query = "UPDATE books SET available = available + 1 WHERE id = :book_id";
        $book_stmt = $db->prepare($book_query);
        $book_stmt->bindParam(':book_id', $borrow['book_id']);
        
        if ($update_stmt->execute() && $book_stmt->execute()) {
            $message = "Book returned successfully!";
            $message_type = "success";
        } else {
            $message = "Failed to return book.";
            $message_type = "danger";
        }
    }
}


$query = "SELECT b.*, bk.title as book_title, bk.isbn, u.full_name as user_name
          FROM borrowings b
          JOIN books bk ON b.book_id = bk.id
          JOIN users u ON b.user_id = u.id
          ORDER BY b.borrow_date DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$borrowings = $stmt->fetchAll(PDO::FETCH_ASSOC);


$stats_query = "SELECT 
    COUNT(*) as total_borrowings,
    SUM(CASE WHEN return_date IS NULL THEN 1 ELSE 0 END) as active_borrowings,
    SUM(CASE WHEN return_date IS NOT NULL THEN 1 ELSE 0 END) as returned_books,
    SUM(CASE WHEN return_date IS NULL AND due_date < CURDATE() THEN 1 ELSE 0 END) as overdue_books
FROM borrowings";
$stats_stmt = $db->prepare($stats_query);
$stats_stmt->execute();
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow History - Librarian Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="glass-container">
        <div class="glass-card">
            <div class="dashboard-header">
                <div class="user-info">
                    <h2><i class="fas fa-history"></i> Borrow History</h2>
                    <span class="user-role">Librarian</span>
                </div>
                <div>
                    <a href="dashboard.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                    <a href="../../index.php" class="btn btn-danger">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>

            <?php if (isset($message)): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="stats-grid" style="margin-bottom: 30px;">
                <div class="stat-card">
                    <i class="fas fa-clipboard-list"></i>
                    <h3><?php echo $stats['total_borrowings']; ?></h3>
                    <p>Total Borrowings</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-book"></i>
                    <h3><?php echo $stats['active_borrowings']; ?></h3>
                    <p>Currently Borrowed</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-check-circle"></i>
                    <h3><?php echo $stats['returned_books']; ?></h3>
                    <p>Returned</p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3><?php echo $stats['overdue_books']; ?></h3>
                    <p>Overdue</p>
                </div>
            </div>

            <h3 style="color: white; margin-bottom: 20px;">
                <i class="fas fa-list"></i> All Borrow Records (<?php echo count($borrowings); ?>)
            </h3>
            
            <?php if (empty($borrowings)): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> No borrow records found.
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Book</th>
                                <th>Borrowed By</th>
                                <th>Borrow Date</th>
                                <th>Due Date</th>
                                <th>Return Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($borrowings as $borrow): 
                                $today = date('Y-m-d');
                                $due_date = $borrow['due_date'];
                                $status = '';
                                $status_color = '';
                                
                                if ($borrow['return_date']) {
                                    $status = 'Returned';
                                    $status_color = 'rgba(40, 167, 69, 0.3)'; 
                                } elseif ($due_date < $today) {
                                    $status = 'Overdue';
                                    $status_color = 'rgba(220, 53, 69, 0.3)'; 
                                } else {
                                    $status = 'Borrowed';
                                    $status_color = 'rgba(255, 193, 7, 0.3)'; 
                                }
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($borrow['book_title']); ?></strong><br>
                                    <small style="color: rgba(255,255,255,0.6);">ISBN: <?php echo $borrow['isbn']; ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($borrow['user_name']); ?></td>
                                <td><?php echo $borrow['borrow_date']; ?></td>
                                <td><?php echo $borrow['due_date']; ?></td>
                                <td>
                                    <?php if ($borrow['return_date']): ?>
                                        <span style="color: #96c93d;">
                                            <?php echo $borrow['return_date']; ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: rgba(255,255,255,0.6);">
                                            Not Returned
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="user-role" style="background: <?php echo $status_color; ?>;">
                                        <?php echo $status; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!$borrow['return_date']): ?>
                                        <a href="view_history.php?return=<?php echo $borrow['id']; ?>" 
                                           class="btn btn-success btn-sm"
                                           onclick="return confirm('Mark this book as returned?')">
                                            <i class="fas fa-check"></i> Mark Returned
                                        </a>
                                    <?php else: ?>
                                        <span class="user-role" style="background: rgba(108, 117, 125, 0.3);">
                                            Already Returned
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                
                <div style="margin-top: 20px; background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px;">
                    <h4 style="color: white; margin-bottom: 10px;">
                        <i class="fas fa-info-circle"></i> How to Return Books
                    </h4>
                    <p style="color: rgba(255,255,255,0.8); margin: 5px 0;">
                        1. Click "Mark Returned" for the borrowed book<br>
                        2. The return date will be set to today<br>
                        3. Book availability will be updated automatically<br>
                        4. Overdue books will be marked as returned
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>