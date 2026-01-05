<?php
session_start();
require_once '../../config/database.php';
 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'librarian') {
    header("Location: ../../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$message = "";
$message_type = "";

if (isset($_POST['add_book'])) {
    $isbn = $_POST['isbn'];
    $title = $_POST['title'];
    $author = $_POST['author'];
    $quantity = $_POST['quantity'];
    
    $query = "INSERT INTO books (isbn, title, author, quantity, available) 
              VALUES (:isbn, :title, :author, :quantity, :quantity)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':isbn', $isbn);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':author', $author);
    $stmt->bindParam(':quantity', $quantity);
    
    if ($stmt->execute()) {
        $message = "Book added successfully!";
        $message_type = "success";
    } else {
        $message = "Failed to add book.";
        $message_type = "danger";
    }
}


if (isset($_POST['update_book'])) {
    $book_id = $_POST['book_id'];
    $isbn = $_POST['isbn'];
    $title = $_POST['title'];
    $author = $_POST['author'];
    $quantity = $_POST['quantity'];
    
    $query = "UPDATE books SET isbn = :isbn, title = :title, author = :author, quantity = :quantity 
              WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':isbn', $isbn);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':author', $author);
    $stmt->bindParam(':quantity', $quantity);
    $stmt->bindParam(':id', $book_id);
    
    if ($stmt->execute()) {
        $message = "Book updated successfully!";
        $message_type = "success";
    } else {
        $message = "Failed to update book.";
        $message_type = "danger";
    }
}


if (isset($_GET['delete'])) {
    $book_id = $_GET['delete'];
    
    $query = "DELETE FROM books WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $book_id);
    
    if ($stmt->execute()) {
        $message = "Book deleted successfully!";
        $message_type = "success";
    } else {
        $message = "Failed to delete book.";
        $message_type = "danger";
    }
}


$query = "SELECT * FROM books ORDER BY title";
$stmt = $db->prepare($query);
$stmt->execute();
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);


$edit_book = null;
if (isset($_GET['edit'])) {
    $book_id = $_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->execute([$book_id]);
    $edit_book = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books - Librarian Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="glass-container">
        <div class="glass-card">
            <div class="dashboard-header">
                <div class="user-info">
                    <h2><i class="fas fa-book"></i> Manage Books</h2>
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

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            
            <h3 style="color: white; margin-bottom: 20px;">
                <i class="fas fa-<?php echo $edit_book ? 'edit' : 'plus'; ?>"></i>
                <?php echo $edit_book ? 'Edit Book' : 'Add New Book'; ?>
            </h3>
            
            <form method="POST" action="" style="margin-bottom: 30px;">
                <?php if ($edit_book): ?>
                    <input type="hidden" name="book_id" value="<?php echo $edit_book['id']; ?>">
                <?php endif; ?>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label for="isbn">ISBN</label>
                        <input type="text" id="isbn" name="isbn" class="form-control" required
                               value="<?php echo $edit_book ? htmlspecialchars($edit_book['isbn']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="quantity">Quantity</label>
                        <input type="number" id="quantity" name="quantity" class="form-control" required min="1"
                               value="<?php echo $edit_book ? $edit_book['quantity'] : '1'; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="title">Book Title</label>
                    <input type="text" id="title" name="title" class="form-control" required
                           value="<?php echo $edit_book ? htmlspecialchars($edit_book['title']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="author">Author</label>
                    <input type="text" id="author" name="author" class="form-control" required
                           value="<?php echo $edit_book ? htmlspecialchars($edit_book['author']) : ''; ?>">
                </div>
                
                <button type="submit" name="<?php echo $edit_book ? 'update_book' : 'add_book'; ?>" 
                        class="btn btn-<?php echo $edit_book ? 'success' : 'primary'; ?>">
                    <i class="fas fa-<?php echo $edit_book ? 'save' : 'plus'; ?>"></i>
                    <?php echo $edit_book ? 'Update Book' : 'Add Book'; ?>
                </button>
                
                <?php if ($edit_book): ?>
                    <a href="manage_books.php" class="btn btn-warning">
                        <i class="fas fa-times"></i> Cancel Edit
                    </a>
                <?php endif; ?>
            </form>

            
            <h3 style="color: white; margin-bottom: 20px;">
                <i class="fas fa-list"></i> All Books (<?php echo count($books); ?>)
            </h3>
            
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
                                        <a href="manage_books.php?edit=<?php echo $book['id']; ?>" class="btn btn-success btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="borrow_book.php?book_id=<?php echo $book['id']; ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-exchange-alt"></i> Borrow
                                        </a>
                                        <a href="manage_books.php?delete=<?php echo $book['id']; ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Delete this book?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>