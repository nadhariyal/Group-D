<?php
session_start();
require_once '../../config/database.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'librarian') {
    header("Location: ../../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$book_id = isset($_GET['book_id']) ? $_GET['book_id'] : 0;

$book_query = "SELECT * FROM books WHERE id = :id";
$book_stmt = $db->prepare($book_query);
$book_stmt->bindParam(':id', $book_id);
$book_stmt->execute();
$book = $book_stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    header("Location: dashboard.php");
    exit();
}


$users_query = "SELECT * FROM users ORDER BY full_name";
$users_stmt = $db->prepare($users_query);
$users_stmt->execute();
$users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);

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

if (isset($_POST['borrow'])) {
    $user_id = $_POST['user_id'];
    $due_date = $_POST['due_date'];
    
    
    if ($book['available'] > 0) {
          
        $borrow_query = "INSERT INTO borrowings (book_id, user_id, borrow_date, due_date) 
                         VALUES (:book_id, :user_id, CURDATE(), :due_date)";
        $borrow_stmt = $db->prepare($borrow_query);
        $borrow_stmt->bindParam(':book_id', $book_id);
        $borrow_stmt->bindParam(':user_id', $user_id);
        $borrow_stmt->bindParam(':due_date', $due_date);
        
        
        $update_query = "UPDATE books SET available = available - 1 WHERE id = :id";
        $update_stmt = $db->prepare($update_query);
        $update_stmt->bindParam(':id', $book_id);
        
        if ($borrow_stmt->execute() && $update_stmt->execute()) {
            $message = "Book borrowed successfully!";
            $message_type = "success";
            
            $book_stmt->execute();
            $book = $book_stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $message = "Failed to borrow book.";
            $message_type = "danger";
        }
    } else {
        $message = "Book is not available for borrowing.";
        $message_type = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow Book - Librarian Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="glass-container">
        <div class="glass-card">
            <div class="dashboard-header">
                <div class="user-info">
                    <h2><i class="fas fa-exchange-alt"></i> Borrow Book</h2>
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

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                
                <div>
                    <h3 style="color: white; margin-bottom: 20px;">
                        <i class="fas fa-book"></i> Book Details
                    </h3>
                    
                    <div style="background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px;">
                        <h4 style="color: white;"><?php echo htmlspecialchars($book['title']); ?></h4>
                        <p style="color: rgba(255,255,255,0.8);">
                            <strong>ISBN:</strong> <?php echo htmlspecialchars($book['isbn']); ?><br>
                            <strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?><br>
                            <strong>Total Copies:</strong> <?php echo $book['quantity']; ?><br>
                            <strong>Available:</strong> 
                            <span style="color: <?php echo $book['available'] > 0 ? '#96c93d' : '#dc3545'; ?>;">
                                <?php echo $book['available']; ?>
                            </span>
                        </p>
                    </div>
                </div>

                
                <div>
                    <h3 style="color: white; margin-bottom: 20px;">
                        <i class="fas fa-user-plus"></i> Borrow to User
                    </h3>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="user_id">Select User</label>
                            <select id="user_id" name="user_id" class="form-control" required>
                                <option value="">-- Select User --</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?php echo $user['id']; ?>">
                                        <?php echo htmlspecialchars($user['full_name']) . ' (' . $user['username'] . ')'; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="due_date">Due Date</label>
                            <?php 
                            $default_due_date = date('Y-m-d', strtotime('+14 days')); 
                            ?>
                            <input type="date" id="due_date" name="due_date" class="form-control" required
                                   value="<?php echo $default_due_date; ?>"
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <button type="submit" name="borrow" class="btn btn-success btn-block"
                                <?php echo ($book['available'] <= 0) ? 'disabled' : ''; ?>>
                            <i class="fas fa-exchange-alt"></i> Borrow Book
                        </button>
                        
                        <?php if ($book['available'] <= 0): ?>
                            <div class="alert alert-danger" style="margin-top: 15px;">
                                <i class="fas fa-exclamation-circle"></i> This book is currently not available.
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>