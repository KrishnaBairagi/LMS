<?php
session_start();
include 'config/db.php';
include 'includes/header.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] === 'admin') {
    header('Location: dashboard.php');
    exit();
}

$user_id = intval($_SESSION['user_id']);
?>

<div class="main-content">
    <!-- Page Header -->
    <div class="mb-4">
        <h2><i class="fas fa-tasks me-2"></i>My Book Requests Status</h2>
        <p class="text-muted mb-0">Track the status of all your book requests</p>
    </div>

    <!-- Status Overview -->
    <div class="row mb-4">
        <?php
        $pending = $conn->query("SELECT COUNT(*) as count FROM book_requests WHERE user_id=$user_id AND status='pending'")->fetch_assoc()['count'];
        $approved = $conn->query("SELECT COUNT(*) as count FROM book_requests WHERE user_id=$user_id AND status='approved'")->fetch_assoc()['count'];
        $rejected = $conn->query("SELECT COUNT(*) as count FROM book_requests WHERE user_id=$user_id AND status='rejected'")->fetch_assoc()['count'];
        ?>

        <div class="col-md-4 mb-3">
            <div class="stat-card bg-issued shadow">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6>Pending</h6>
                        <h2><?= $pending ?></h2>
                        <small class="text-white-50">Awaiting approval</small>
                    </div>
                    <div style="font-size: 40px; opacity: 0.8;">⏳</div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="stat-card bg-success shadow">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6>Approved</h6>
                        <h2><?= $approved ?></h2>
                        <small class="text-white-50">Ready to use</small>
                    </div>
                    <div style="font-size: 40px; opacity: 0.8;">✅</div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="stat-card bg-danger shadow">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6>Rejected</h6>
                        <h2><?= $rejected ?></h2>
                        <small class="text-white-50">Not approved</small>
                    </div>
                    <div style="font-size: 40px; opacity: 0.8;">❌</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Requests Table -->
    <div class="card shadow">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>All Your Requests</h5>
        </div>
        <div class="card-body">
            <?php
            $all_requests = $conn->query("
                SELECT br.id, b.title, b.author, br.status, br.request_date, br.approved_date, br.approval_notes, u.name as approved_by
                FROM book_requests br
                JOIN books b ON br.book_id = b.id
                LEFT JOIN users u ON br.approved_by = u.id
                WHERE br.user_id = $user_id
                ORDER BY br.request_date DESC
            ");

            if ($all_requests && $all_requests->num_rows > 0):
            ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th><i class="fas fa-book me-2"></i>Book Title</th>
                                <th><i class="fas fa-user me-2"></i>Author</th>
                                <th><i class="fas fa-calendar me-2"></i>Requested</th>
                                <th><i class="fas fa-badge me-2"></i>Status</th>
                                <th><i class="fas fa-info me-2"></i>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($req = $all_requests->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($req['title']) ?></strong></td>
                                    <td><?= htmlspecialchars($req['author']) ?></td>
                                    <td><small><?= date('M d, Y', strtotime($req['request_date'])) ?></small></td>
                                    <td>
                                        <?php
                                        $status = $req['status'];
                                        $badge_class = [
                                            'pending' => 'warning text-dark',
                                            'approved' => 'success',
                                            'rejected' => 'danger'
                                        ][$status] ?? 'secondary';
                                        
                                        $status_icon = [
                                            'pending' => '⏳',
                                            'approved' => '✅',
                                            'rejected' => '❌'
                                        ][$status] ?? '•';
                                        ?>
                                        <span class="badge bg-<?= $badge_class ?>">
                                            <?= $status_icon ?> <?= ucfirst($status) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($req['status'] === 'approved' && $req['approved_date']): ?>
                                            <small class="text-muted">Approved on <?= date('M d, Y', strtotime($req['approved_date'])) ?></small>
                                        <?php elseif ($req['status'] === 'rejected' && $req['approval_notes']): ?>
                                            <small class="text-muted">Reason: <?= htmlspecialchars($req['approval_notes']) ?></small>
                                        <?php else: ?>
                                            <small class="text-muted">-</small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>No requests yet</strong>
                    <p class="mb-0 mt-2">
                        <a href="request_book.php">Browse and request books</a>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Help Section -->
    <div class="card shadow mt-4">
        <div class="card-header bg-info">
            <h5 class="mb-0"><i class="fas fa-question-circle me-2"></i>What's Next?</h5>
        </div>
        <div class="card-body">
            <h6>Understanding Request Status:</h6>
            <ul>
                <li><strong>⏳ Pending:</strong> Your request is awaiting admin approval</li>
                <li><strong>✅ Approved:</strong> The book has been issued to you! Check "My Books & Penalties" to see it</li>
                <li><strong>❌ Rejected:</strong> The admin could not approve your request</li>
            </ul>

            <h6 class="mt-3">Quick Links:</h6>
            <p>
                <a href="request_book.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-paper-plane me-1"></i>Request More Books
                </a>
                <a href="issued_book.php" class="btn btn-success btn-sm">
                    <i class="fas fa-book-open me-1"></i>My Approved Books
                </a>
                <a href="troubleshoot_requests.php" class="btn btn-warning btn-sm">
                    <i class="fas fa-wrench me-1"></i>Troubleshoot
                </a>
            </p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
