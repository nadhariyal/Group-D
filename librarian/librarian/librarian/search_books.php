<?php
session_start();
require_once '../../config/database.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'librarian') {
    header("Location: ../../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$search_results = [];
$search_query = "";

if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
    
    $query = "SELECT * FROM books 
              WHERE title LIKE :search 
              OR author LIKE :search 
              OR isbn LIKE :search 
              ORDER BY title";
    $stmt = $db->prepare($query);
    $search_term = "%" . $search_query . "%";
    $stmt->bindParam(':search', $search_term);
    $stmt->execute();
    $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Books - Librarian Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="glass-container">
        <div class="glass-card">
            <div class="dashboard-header">
                <div class="user-info">
                    <h2><i class="fas fa-search"></i> Search Books</h2>
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

            
            <h3 style="color: white; margin-bottom: 20px;">
                <i class="fas fa-search"></i> Search Library Catalog
            </h3>
            
            <form method="GET" action="" style="margin-bottom: 30px;">
                <div class="form-group">
                    <input type="text" name="search" class="form-control" 
                           value="<?php echo htmlspecialchars($search_query); ?>"
                           placeholder="Search by title, author, or ISBN...">
                </div>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-search"></i> Search
                </button>
            </form>

            
            <?php if ($search_query): ?>
                <h3 style="color: white; margin-bottom: 20px;">
                    <i class="fas fa-list"></i> Search Results for "<?php echo htmlspecialchars($search_query); ?>"
                    (<?php echo count($search_results); ?>)
                </h3>
                
                <?php if (empty($search_results)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> No books found matching your search.
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ISBN</th>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Available</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($search_results as $book): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                                    <td><?php echo htmlspecialchars($book['title']); ?></td>
                                    <td><?php echo htmlspecialchars($book['author']); ?></td>
                                    <td>
                                        <span class="user-role" style="background: <?php 
                                            echo $book['available'] > 0 ? 'rgba(40, 167, 69, 0.3)' : 'rgba(220, 53, 69, 0.3)';
                                        ?>;">
                                            <?php echo $book['available']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="borrow_book.php?book_id=<?php echo $book['id']; ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-exchange-alt"></i> Borrow
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>