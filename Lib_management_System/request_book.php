<?php
session_start();
include 'config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

// Prevent admin from requesting (they should manage approvals)
if ($_SESSION['role'] === 'admin') {
    header('Location: manage_requests.php');
    exit();
}

// CRITICAL: Ensure book_requests table exists BEFORE any queries
$tableCheck = $conn->query("SHOW TABLES LIKE 'book_requests'");
if (!$tableCheck || $tableCheck->num_rows === 0) {
    // Create the table
    $createTable = "CREATE TABLE IF NOT EXISTS book_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        book_id INT NOT NULL,
        request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        approved_date DATETIME NULL,
        approval_notes TEXT,
        status ENUM('pending','approved','rejected') DEFAULT 'pending',
        approved_by INT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (book_id) REFERENCES books(id),
        FOREIGN KEY (approved_by) REFERENCES users(id)
    )";
    
    if (!$conn->query($createTable)) {
        die("Error creating book_requests table: " . $conn->error);
    }
}

include 'includes/header.php';

$user_id = intval($_SESSION['user_id']);
$success = "";
$error = "";

// Handle request submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $book_id = isset($_POST['book_id']) ? intval($_POST['book_id']) : 0;
    
    error_log("REQUEST DEBUG: User ID: $user_id, Book ID: $book_id, Method: " . $_SERVER["REQUEST_METHOD"]);
    
    if ($book_id <= 0) {
        $error = "❌ Invalid book selected (ID: $book_id)";
    } else {
        // Check if book exists and is available
        $bookCheck = $conn->query("SELECT available, title FROM books WHERE id=$book_id");
        
        if (!$bookCheck || $bookCheck->num_rows == 0) {
            $error = "❌ Book not found (ID: $book_id)";
        } else {
            $book = $bookCheck->fetch_assoc();
            if ($book['available'] <= 0) {
                $error = "❌ '" . htmlspecialchars($book['title']) . "' is currently not available";
            } else {
                // Check if user already has a pending request for this book
                $existingRequest = $conn->query("SELECT id FROM book_requests WHERE user_id=$user_id AND book_id=$book_id AND status='pending'");
                
                if ($existingRequest && $existingRequest->num_rows > 0) {
                    $error = "⚠️ You already have a pending request for this book";
                } else {
                    // Create new request - with debug
                    $insertSQL = "INSERT INTO book_requests (user_id, book_id, status, request_date) VALUES ($user_id, $book_id, 'pending', NOW())";
                    error_log("EXECUTING: $insertSQL");
                    
                    $insertRequest = $conn->query($insertSQL);
                    
                    if ($insertRequest) {
                        $request_id = $conn->insert_id;
                        $success = "✅ Book request submitted! Request ID: $request_id. Waiting for admin approval.";
                        error_log("SUCCESS: Request created with ID $request_id");
                    } else {
                        $error = "❌ Error submitting request: " . $conn->error;
                        error_log("ERROR: " . $conn->error);
                    }
                }
            }
        }
    }
}

// Get all available books with request status for current user
$booksQuery = $conn->query("
    SELECT 
        b.id, 
        b.title, 
        b.author, 
        b.category, 
        b.available, 
        b.image,
        COALESCE(br.id, 0) as request_id,
        COALESCE(br.status, 'none') as request_status
    FROM books b
    LEFT JOIN book_requests br ON (b.id = br.book_id AND br.user_id = $user_id AND br.status IN ('pending', 'approved'))
    ORDER BY b.title
");

if (!$booksQuery) {
    $error = "Error loading books: " . $conn->error;
}

// Get pending requests for current user
$pendingQuery = $conn->query("
    SELECT COUNT(*) as count FROM book_requests WHERE user_id=$user_id AND status='pending'
");
$pending = ($pendingQuery) ? $pendingQuery->fetch_assoc()['count'] : 0;

// Get approved requests for current user
$approvedQuery = $conn->query("
    SELECT COUNT(*) as count FROM book_requests WHERE user_id=$user_id AND status='approved'
");
$approved = ($approvedQuery) ? $approvedQuery->fetch_assoc()['count'] : 0;
?>

<div class="main-content">
    <!-- Page Header -->
    <div class="mb-4">
        <h2><i class="fas fa-book-open me-2"></i>Request Books</h2>
        <p class="text-muted mb-0">Browse and request available books from our library</p>
    </div>

    <!-- Messages -->
    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $success ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Request Status Cards -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="stat-card bg-issued shadow">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6>Pending Requests</h6>
                        <h2><?= $pending ?></h2>
                        <small class="text-white-50">Awaiting approval</small>
                    </div>
                    <div style="font-size: 40px; opacity: 0.8;">⏳</div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="stat-card bg-users shadow">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6>Approved Requests</h6>
                        <h2><?= $approved ?></h2>
                        <small class="text-white-50">Ready to pick up</small>
                    </div>
                    <div style="font-size: 40px; opacity: 0.8;">✅</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Available Books Grid -->
    <div class="mb-4">
        <h4 class="mb-3"><i class="fas fa-library me-2"></i>Available Books</h4>
        
        <?php if ($booksQuery->num_rows > 0): ?>
            <div class="row">
                <?php while ($book = $booksQuery->fetch_assoc()): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 shadow book-request-card">
                            <?php if (!empty($book['image'])): ?>
                                <div style="height: 200px; background: linear-gradient(135deg, #6c84ff, #4969ff); display: flex; align-items: center; justify-content: center; color: white; font-size: 60px; overflow: hidden;">
                                    <img src="<?= htmlspecialchars($book['image']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                            <?php else: ?>
                                <div style="height: 200px; background: linear-gradient(135deg, #6c84ff, #4969ff); display: flex; align-items: center; justify-content: center; color: white; font-size: 60px;">
                                    📚
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($book['title']) ?></h5>
                                <p class="card-text text-muted">
                                    <i class="fas fa-user me-1"></i><?= htmlspecialchars($book['author']) ?>
                                </p>
                                <p class="card-text">
                                    <span class="badge bg-info"><?= htmlspecialchars($book['category']) ?></span>
                                </p>
                                
                                <div class="mb-3">
                                    <?php if ($book['available'] > 0): ?>
                                        <small class="text-success">
                                            <i class="fas fa-check-circle me-1"></i><?= $book['available'] ?> Available
                                        </small>
                                    <?php else: ?>
                                        <small class="text-danger">
                                            <i class="fas fa-times-circle me-1"></i>Not Available
                                        </small>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Request Status -->
                                <?php 
                                $request_status = $book['request_status'];
                                
                                if ($request_status === 'pending'): ?>
                                    <span class="badge bg-warning w-100 text-dark" style="margin-top: 10px;">
                                        <i class="fas fa-hourglass-half me-1"></i>Request Pending
                                    </span>
                                <?php elseif ($request_status === 'approved'): ?>
                                    <span class="badge bg-success w-100" style="margin-top: 10px;">
                                        <i class="fas fa-check-circle me-1"></i>Request Approved
                                    </span>
                                <?php else: // No request yet (status = 'none') ?>
                                    <form method="POST" style="margin-top: 10px;">
                                        <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                                        <button type="submit" class="btn btn-primary btn-sm w-100" <?= ($book['available'] <= 0) ? 'disabled' : '' ?>>
                                            <i class="fas fa-paper-plane me-1"></i>Request Book
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle me-2"></i>
                <strong>No books available</strong>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.book-request-card {
    border: 1px solid #3a4555;
    transition: transform 0.3s, box-shadow 0.3s;
}

.book-request-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(108, 132, 255, 0.3);
}
</style>

<?php include 'includes/footer.php'; ?>
