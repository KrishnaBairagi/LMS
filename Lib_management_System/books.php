<?php
session_start();
include 'config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

include 'includes/header.php';

$result = $conn->query("SELECT * FROM books ORDER BY created_at DESC");
?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>📚 Manage Books</h3>
        <a href="add_book.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Add New Book
        </a>
    </div>

    <!-- Books Grid -->
    <div class="row">
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="book-card shadow">
                        <div class="book-image-container">
                            <?php if (!empty($row['image'])): ?>
                                <img src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['title']) ?>" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%25%22 height=%22100%25%22%3E%3Crect fill=%22%23ddd%22 width=%22100%25%22 height=%22100%25%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-family=%22Arial%22 font-size=%2224%22 fill=%22%23999%22%3E📖%3C/text%3E%3C/svg%3E'">
                            <?php else: ?>
                                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 50px;">
                                    📖
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="book-card-body">
                            <h5 class="book-title"><?= htmlspecialchars($row['title']) ?></h5>
                            <p class="book-author">by <?= htmlspecialchars($row['author']) ?></p>
                            <div class="book-category"><?= htmlspecialchars($row['category']) ?></div>
                            <p class="book-qty">
                                <strong>Quantity:</strong> <?= $row['quantity'] ?> | 
                                <strong class="available">Available:</strong> <?= $row['available'] ?>
                            </p>
                            <div class="btn-group">
                                <?php if ($_SESSION['role'] === 'admin'): ?>
                                    <a href="delete_book.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                <?php else: ?>
                                    <a href="request_book.php" class="btn btn-primary btn-sm">
                                        <i class="fas fa-paper-plane"></i> Request
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info" role="alert">
                    <i class="fas fa-info-circle me-2"></i> No books found. <a href="add_book.php">Add one now!</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
