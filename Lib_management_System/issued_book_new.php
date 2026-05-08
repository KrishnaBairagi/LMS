<?php
session_start();
include 'config/db.php';
include 'includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$user_id = intval($_SESSION['user_id']);
$today = date('Y-m-d');

// Get issued books for the current user with better error handling
$query = "SELECT 
    ib.id,
    ib.book_id, 
    b.title, 
    b.author, 
    ib.issue_date, 
    ib.return_date, 
    ib.status
FROM issued_books ib
INNER JOIN books b ON ib.book_id = b.id  
WHERE ib.user_id = $user_id
ORDER BY ib.issue_date DESC";

$result = $conn->query($query);

if (!$result) {
    echo "<div class='main-content'>";
    echo "<div class='alert alert-danger'>";
    echo "<strong>Error loading books:</strong> " . htmlspecialchars($conn->error);
    echo "</div>";
    echo "</div>";
    include 'includes/footer.php';
    exit();
}

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total_issued,
    SUM(CASE WHEN status='issued' THEN 1 ELSE 0 END) as currently_issued,
    SUM(CASE WHEN status='returned' THEN 1 ELSE 0 END) as returned_count
FROM issued_books 
WHERE user_id = $user_id";

$stats_result = $conn->query($stats_query);
$stats = $stats_result ? $stats_result->fetch_assoc() : ['total_issued' => 0, 'currently_issued' => 0, 'returned_count' => 0];

// Get total penalties
$penalty_query = "SELECT 
    COALESCE(SUM(CASE 
        WHEN status='issued' AND return_date < '$today' 
        THEN CEIL(DATEDIFF('$today', return_date)) * 10 
        ELSE 0 
    END), 0) as total_penalty
FROM issued_books 
WHERE user_id = $user_id";

$penalty_result = $conn->query($penalty_query);
$penalty_data = $penalty_result ? $penalty_result->fetch_assoc() : ['total_penalty' => 0];
$total_fine = intval($penalty_data['total_penalty'] ?? 0);
?>

<div class="main-content">
    <!-- Page Header -->
    <div class="mb-4">
        <h2><i class="fas fa-book-open me-2"></i>My Issued Books & Penalties</h2>
        <p class="text-muted mb-0">View your library book history and outstanding penalties</p>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stat-card bg-books shadow">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6>Total Issued</h6>
                        <h2><?= $stats['total_issued'] ?? 0 ?></h2>
                        <small class="text-white-50">All time</small>
                    </div>
                    <div style="font-size: 40px; opacity: 0.8;">📚</div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="stat-card bg-issued shadow">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6>Currently Issued</h6>
                        <h2><?= $stats['currently_issued'] ?? 0 ?></h2>
                        <small class="text-white-50">Active books</small>
                    </div>
                    <div style="font-size: 40px; opacity: 0.8;">📖</div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="stat-card bg-users shadow">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6>Returned</h6>
                        <h2><?= $stats['returned_count'] ?? 0 ?></h2>
                        <small class="text-white-50">Completed</small>
                    </div>
                    <div style="font-size: 40px; opacity: 0.8;">✅</div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="stat-card <?= ($total_fine > 0) ? 'bg-danger' : 'bg-success' ?> shadow">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6>Pending Penalties</h6>
                        <h2>₹<?= $total_fine ?></h2>
                        <small class="text-white-50">Amount due</small>
                    </div>
                    <div style="font-size: 40px; opacity: 0.8;"><?= ($total_fine > 0) ? '⚠️' : '✨' ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- My Books Table -->
    <div class="card shadow">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Book History</h5>
        </div>
        <div class="card-body">
            <?php if ($result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th><i class="fas fa-book me-2"></i>Title</th>
                                <th><i class="fas fa-user-edit me-2"></i>Author</th>
                                <th><i class="fas fa-calendar-alt me-2"></i>Issue Date</th>
                                <th><i class="fas fa-calendar-check me-2"></i>Return Date</th>
                                <th><i class="fas fa-badge me-2"></i>Status</th>
                                <th><i class="fas fa-money-bill me-2"></i>Penalty</th>
                                <th><i class="fas fa-cogs me-2"></i>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            while($row = $result->fetch_assoc()): 
                                $is_overdue = ($today > $row['return_date'] && $row['status'] == 'issued');
                                $penalty = 0;
                                if ($is_overdue) {
                                    $days_late = ceil((strtotime($today) - strtotime($row['return_date'])) / 86400);
                                    $penalty = $days_late * 10;
                                }
                            ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($row['title']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['author']) ?></td>
                                    <td><small><?= date('M d, Y', strtotime($row['issue_date'])) ?></small></td>
                                    <td><small><?= date('M d, Y', strtotime($row['return_date'])) ?></small></td>
                                    <td>
                                        <?php if ($row['status'] == 'issued'): ?>
                                            <?php if ($is_overdue): ?>
                                                <span class="badge bg-danger"><i class="fas fa-exclamation-triangle me-1"></i>Overdue</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning"><i class="fas fa-hourglass-half me-1"></i>Issued</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Returned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($penalty > 0): ?>
                                            <span class="badge bg-danger">₹<?= $penalty ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-success">₹0</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($row['status'] == 'issued'): ?>
                                            <a href="return.php?id=<?= $row['id'] ?>" class="btn btn-success btn-sm">
                                                <i class="fas fa-undo me-1"></i>Return
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Returned</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center" style="margin: 0;">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>No books issued yet</strong>
                    <p class="mb-0 mt-2">Request books from the library and they will appear here once approved.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
