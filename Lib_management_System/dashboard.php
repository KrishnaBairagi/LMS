<?php
session_start();
include 'config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

include 'includes/header.php';

$totalBooks = $conn->query("SELECT COUNT(*) as total FROM books")->fetch_assoc()['total'];
$totalUsers = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
$totalIssued = $conn->query("SELECT COUNT(*) as total FROM issued_books WHERE status='issued'")->fetch_assoc()['total'];
$totalReturned = $conn->query("SELECT COUNT(*) as total FROM issued_books WHERE status='returned'")->fetch_assoc()['total'];

// Get recent books
$recentBooks = $conn->query("SELECT * FROM books ORDER BY created_at DESC LIMIT 5");
?>

<div class="main-content">
    <!-- Page Header -->
    <div class="mb-4">
        <h2 class="d-inline-block">📊 Dashboard</h2>
        <p class="text-muted mb-0">Welcome back! Here's your library overview.</p>
    </div>

    <!-- Welcome Card -->
    <div class="welcome-card mb-4">
        <div class="row">
            <div class="col-md-8">
                <h5>👋 Welcome to Library Management System</h5>
                <p class="text-muted mb-0">Manage your library efficiently. Monitor books, users, and issued records all in one place.</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="books.php" class="btn btn-primary">✨ Browse Books</a>
            </div>
        </div>
    </div>

    <!-- Statistics Row -->
    <div class="row mb-4">
        <!-- Total Books -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="stat-card bg-books shadow">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6>Total Books</h6>
                        <h2><?= $totalBooks ?></h2>
                        <small class="text-white-50">Books in library</small>
                    </div>
                    <div style="font-size: 50px; opacity: 0.8;">📚</div>
                </div>
            </div>
        </div>

        <!-- Total Users -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="stat-card bg-users shadow">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6>Total Users</h6>
                        <h2><?= $totalUsers ?></h2>
                        <small class="text-white-50">Active members</small>
                    </div>
                    <div style="font-size: 50px; opacity: 0.8;">👥</div>
                </div>
            </div>
        </div>

        <!-- Books Issued -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="stat-card bg-issued shadow">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6>Books Issued</h6>
                        <h2><?= $totalIssued ?></h2>
                        <small class="text-white-50">Currently issued</small>
                    </div>
                    <div style="font-size: 50px; opacity: 0.8;">📤</div>
                </div>
            </div>
        </div>

        <!-- Books Returned -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="stat-card bg-returned shadow">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6>Books Returned</h6>
                        <h2><?= $totalReturned ?></h2>
                        <small class="text-white-50">Total returns</small>
                    </div>
                    <div style="font-size: 50px; opacity: 0.8;">📥</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="welcome-card">
                <h5 class="mb-3">⚡ Quick Actions</h5>
                <div class="btn-group" role="group">
                    <a href="books.php" class="btn btn-primary">
                        <i class="fas fa-book me-2"></i> Manage Books
                    </a>
                    <a href="issue_book.php" class="btn btn-success">
                        <i class="fas fa-arrow-right me-2"></i> Issue Book
                    </a>
                    <a href="issued_books.php" class="btn btn-info">
                        <i class="fas fa-list me-2"></i> View Issued Books
                    </a>
                    <a href="users.php" class="btn btn-warning">
                        <i class="fas fa-users me-2"></i> Manage Users
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Books -->
    <div class="card shadow">
        <div class="card-header">
            <h5 class="mb-0">📖 Recently Added Books</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Available</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recentBooks->num_rows > 0): ?>
                            <?php while($book = $recentBooks->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($book['title']) ?></strong></td>
                                    <td><?= htmlspecialchars($book['author']) ?></td>
                                    <td><span class="badge bg-primary"><?= htmlspecialchars($book['category']) ?></span></td>
                                    <td>
                                        <span class="badge <?= $book['available'] > 0 ? 'bg-success' : 'bg-danger' ?>">
                                            <?= $book['available'] ?> / <?= $book['quantity'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="issue_book.php?book_id=<?= $book['id'] ?>" class="btn btn-sm btn-success">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No books available yet</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
