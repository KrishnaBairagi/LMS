<?php
session_start();
include 'config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$search = isset($_GET['q']) ? htmlspecialchars($_GET['q']) : "";
$result = null;
$books = [];

if (!empty($search)) {
    $result = $conn->query("SELECT * FROM books WHERE title LIKE '%$search%' OR author LIKE '%$search%' OR category LIKE '%$search%' ORDER BY title");
    
    while($row = $result->fetch_assoc()) {
        $books[] = $row;
    }
}

// Check if it's an AJAX request (API call)
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => count($books) > 0,
        'count' => count($books),
        'results' => $books
    ]);
    exit;
}

// Otherwise, render as HTML page
include 'includes/header.php';
?>

<div class="main-content">
    <!-- Page Header -->
    <div class="mb-4">
        <h2><i class="fas fa-search me-2"></i>Search Books</h2>
        <p class="text-muted mb-0">Find books by title, author, or category</p>
    </div>

    <!-- Search Form -->
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-magnifying-glass me-2"></i>Search Library</h5>
        </div>
        <div class="card-body">
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="q" class="form-control form-control-lg" 
                       placeholder="Search by book title, author, or category..." 
                       value="<?= $search ?>" autofocus>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>Search
                </button>
            </form>
        </div>
    </div>

    <!-- Results Section -->
    <?php if (!empty($search)): ?>
        <div class="card shadow">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-results me-2"></i>
                    Results for "<?= htmlspecialchars($search) ?>" 
                    <span class="badge bg-primary ms-2"><?= count($books) ?></span>
                </h5>
            </div>
            <div class="card-body">
                <?php if (count($books) > 0): ?>
                    <div class="row">
                        <?php foreach($books as $book): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card book-card shadow h-100">
                                    <div class="book-image-container">
                                        <?php if (!empty($book['image'])): ?>
                                            <img src="<?= htmlspecialchars($book['image']) ?>" 
                                                 alt="<?= htmlspecialchars($book['title']) ?>" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%25%22 height=%22100%25%22%3E%3Crect fill=%22%23ddd%22 width=%22100%25%22 height=%22100%25%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-family=%22Arial%22 font-size=%2224%22 fill=%22%23999%22%3E📖%3C/text%3E%3C/svg%3E'">
                                        <?php else: ?>
                                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 60px;">📖</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="book-card-body">
                                        <h5 class="book-title"><?= htmlspecialchars($book['title']) ?></h5>
                                        <p class="book-author">by <?= htmlspecialchars($book['author']) ?></p>
                                        <div class="book-category"><?= htmlspecialchars($book['category']) ?></div>
                                        
                                        <div class="book-qty mt-2 mb-3">
                                            <strong>Availability:</strong> 
                                            <span class="<?= $book['available'] > 0 ? 'available' : 'low' ?>">
                                                <?= $book['available'] ?> / <?= $book['quantity'] ?>
                                            </span>
                                        </div>

                                        <div class="btn-group" style="width: 100%;">
                                            <?php if ($book['available'] > 0): ?>
                                                <a href="issue_book.php?book_id=<?= $book['id'] ?>" class="btn btn-success btn-sm flex-grow-1">
                                                    <i class="fas fa-arrow-right me-1"></i>Issue
                                                </a>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-secondary btn-sm flex-grow-1" disabled>
                                                    <i class="fas fa-ban me-1"></i>Not Available
                                                </button>
                                            <?php endif; ?>
                                            <a href="books.php" class="btn btn-info btn-sm flex-grow-1">
                                                <i class="fas fa-info-circle me-1"></i>Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center">
                        <i class="fas fa-search me-2"></i>
                        <strong>No books found</strong>
                        <p class="mb-0 mt-2">Try searching with different keywords</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Start your search</strong>
            <p class="mb-0 mt-2">Enter a book title, author name, or category to find books in our library</p>
        </div>
    <?php endif; ?>
</div>

<style>
.book-card {
    border: none;
    transition: all 0.3s ease;
}

.book-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(108, 132, 255, 0.2) !important;
}

.btn-group {
    display: flex;
    gap: 5px;
}

.btn-group .btn {
    flex: 1;
    padding: 8px 10px;
    font-size: 12px;
}
</style>

<?php include 'includes/footer.php'; ?>
