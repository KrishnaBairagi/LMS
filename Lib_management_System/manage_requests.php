<?php
session_start();
include 'config/db.php';

// Role-based access control
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
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

$message = "";
$error = "";

// Handle request approval/rejection
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];
    $request_id = isset($_POST['request_id']) ? intval($_POST['request_id']) : 0;
    $admin_id = intval($_SESSION['user_id']);
    
    $request = $conn->query("SELECT user_id, book_id FROM book_requests WHERE id=$request_id");
    
    if ($request->num_rows == 0) {
        $error = "Request not found";
    } else {
        $req = $request->fetch_assoc();
        $user_id = intval($req['user_id']);
        $book_id = intval($req['book_id']);
        
        if ($action === 'approve') {
            // Check if book is still available
            $book = $conn->query("SELECT available, title FROM books WHERE id=$book_id")->fetch_assoc();
            
            if (!$book) {
                $error = "Book not found in database";
            } else if ($book['available'] <= 0) {
                $error = "Book '" . htmlspecialchars($book['title']) . "' is no longer available for approval";
            } else {
                // Do NOT update request status yet - first ensure we can issue the book
                $issue_date = date('Y-m-d');
                $return_date = date('Y-m-d', strtotime('+7 days'));
                
                // Try to insert first (this validates the data)
                $insert_result = $conn->query("INSERT INTO issued_books (user_id, book_id, issue_date, return_date, status) VALUES ($user_id, $book_id, '$issue_date', '$return_date', 'issued')");
                
                if (!$insert_result) {
                    $error = "Failed to create issued book record: " . $conn->error;
                } else {
                    // NOW update the request and decrease availability
                    $update1 = $conn->query("UPDATE book_requests SET status='approved', approved_date=NOW(), approved_by=$admin_id WHERE id=$request_id");
                    $update2 = $conn->query("UPDATE books SET available=available-1 WHERE id=$book_id");
                    
                    if ($update1 && $update2) {
                        $message = "✅ Request approved! Book '" . htmlspecialchars($book['title']) . "' has been issued to the user.";
                    } else {
                        $error = "Error updating approval status: " . $conn->error;
                    }
                }
            }
        } 
        else if ($action === 'reject') {
            $notes = isset($_POST['approval_notes']) ? $conn->real_escape_string($_POST['approval_notes']) : "";
            $update = $conn->query("UPDATE book_requests SET status='rejected', approved_date=NOW(), approved_by=$admin_id, approval_notes='$notes' WHERE id=$request_id");
            
            if ($update) {
                $message = "✅ Request rejected and user has been notified.";
            } else {
                $error = "Error processing rejection";
            }
        }
    }
}

// Get all pending requests
$pendingQuery = $conn->query("
    SELECT br.id, br.request_date, u.name as user_name, u.email, b.title, b.author, b.available
    FROM book_requests br
    JOIN users u ON br.user_id = u.id
    JOIN books b ON br.book_id = b.id
    WHERE br.status = 'pending'
    ORDER BY br.request_date DESC
");

// Get all approved requests
$approvedQuery = $conn->query("
    SELECT br.id, br.request_date, br.approved_date, u.name as user_name, b.title, 
           admin.name as admin_name
    FROM book_requests br
    JOIN users u ON br.user_id = u.id
    JOIN books b ON br.book_id = b.id
    LEFT JOIN users admin ON br.approved_by = admin.id
    WHERE br.status = 'approved'
    ORDER BY br.approved_date DESC
    LIMIT 50
");

// Get all rejected requests
$rejectedQuery = $conn->query("
    SELECT br.id, br.request_date, br.approval_notes, u.name as user_name, b.title
    FROM book_requests br
    JOIN users u ON br.user_id = u.id
    JOIN books b ON br.book_id = b.id
    WHERE br.status = 'rejected'
    ORDER BY br.request_date DESC
    LIMIT 20
");

$pending_count = $pendingQuery->num_rows;
?>

<div class="main-content">
    <!-- Page Header -->
    <div class="mb-4">
        <h2><i class="fas fa-tasks me-2"></i>Manage Book Requests</h2>
        <p class="text-muted mb-0">Review and approve/reject user book requests</p>
    </div>

    <!-- Messages -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stat-card bg-issued shadow">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6>Pending Requests</h6>
                        <h2><?= $pending_count ?></h2>
                        <small class="text-white-50">Awaiting review</small>
                    </div>
                    <div style="font-size: 40px; opacity: 0.8;">⏳</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Requests -->
    <div class="card shadow mb-4">
        <div class="card-header bg-danger">
            <h5 class="mb-0"><i class="fas fa-exclamation-circle me-2"></i>Pending Requests (<?= $pending_count ?>)</h5>
        </div>
        <div class="card-body">
            <?php if ($pending_count > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th><i class="fas fa-user me-2"></i>User</th>
                                <th><i class="fas fa-envelope me-2"></i>Email</th>
                                <th><i class="fas fa-book me-2"></i>Book Title</th>
                                <th><i class="fas fa-user-edit me-2"></i>Author</th>
                                <th><i class="fas fa-calendar me-2"></i>Available</th>
                                <th><i class="fas fa-clock me-2"></i>Requested</th>
                                <th><i class="fas fa-cogs me-2"></i>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $pendingQuery->data_seek(0);
                            while ($req = $pendingQuery->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($req['user_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($req['email']) ?></td>
                                    <td><?= htmlspecialchars($req['title']) ?></td>
                                    <td><?= htmlspecialchars($req['author']) ?></td>
                                    <td>
                                        <?php if ($req['available'] > 0): ?>
                                            <span class="badge bg-success"><?= $req['available'] ?> copies</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Not available</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?= date('M d, Y H:i', strtotime($req['request_date'])) ?></small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="approve">
                                                <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                                <button type="submit" class="btn btn-success" title="Approve" <?= ($req['available'] <= 0) ? 'disabled' : '' ?>>
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            
                                            <button type="button" class="btn btn-danger" title="Reject" data-bs-toggle="modal" data-bs-target="#rejectModal<?= $req['id'] ?>">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>

                                        <!-- Reject Modal -->
                                        <div class="modal fade" id="rejectModal<?= $req['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content bg-dark">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Reject Request</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="action" value="reject">
                                                            <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                                            <label class="form-label">Rejection Reason (Optional)</label>
                                                            <textarea name="approval_notes" class="form-control" rows="3" placeholder="Provide a reason for rejection..."></textarea>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-danger">Reject Request</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info mb-0">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>All caught up!</strong>
                    <p class="mb-0 mt-2">There are no pending requests at the moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Approved Requests -->
    <div class="card shadow mb-4">
        <div class="card-header bg-success">
            <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Recently Approved</h5>
        </div>
        <div class="card-body">
            <?php if ($approvedQuery->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th><i class="fas fa-user me-2"></i>User</th>
                                <th><i class="fas fa-book me-2"></i>Book</th>
                                <th><i class="fas fa-calendar me-2"></i>Requested</th>
                                <th><i class="fas fa-check me-2"></i>Approved</th>
                                <th><i class="fas fa-shield me-2"></i>By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($req = $approvedQuery->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($req['user_name']) ?></td>
                                    <td><?= htmlspecialchars($req['title']) ?></td>
                                    <td><small><?= date('M d, Y', strtotime($req['request_date'])) ?></small></td>
                                    <td><small><?= date('M d, Y', strtotime($req['approved_date'])) ?></small></td>
                                    <td><small><?= htmlspecialchars($req['admin_name'] ?? 'System') ?></small></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info mb-0">No approved requests yet</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Rejected Requests -->
    <div class="card shadow">
        <div class="card-header bg-warning">
            <h5 class="mb-0"><i class="fas fa-ban me-2"></i>Rejected Requests</h5>
        </div>
        <div class="card-body">
            <?php if ($rejectedQuery->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th><i class="fas fa-user me-2"></i>User</th>
                                <th><i class="fas fa-book me-2"></i>Book</th>
                                <th><i class="fas fa-comment me-2"></i>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($req = $rejectedQuery->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($req['user_name']) ?></td>
                                    <td><?= htmlspecialchars($req['title']) ?></td>
                                    <td>
                                        <small class="text-muted">
                                            <?= !empty($req['approval_notes']) ? htmlspecialchars($req['approval_notes']) : 'No reason provided' ?>
                                        </small>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info mb-0">No rejected requests</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
