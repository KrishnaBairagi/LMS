<?php
session_start();
include 'config/db.php';

// Role-based access control
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

include 'includes/header.php';

$success = "";
$error = "";

// Handle book return
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['return_id'])) {
    $return_id = $_POST['return_id'];
    
    // Calculate fine if applicable
    $issued = $conn->query("SELECT return_date FROM issued_books WHERE id='$return_id'")->fetch_assoc();
    $return_date = strtotime($issued['return_date']);
    $today = time();
    $days_late = 0;
    $fine = 0;
    
    if ($today > $return_date) {
        $days_late = ceil(($today - $return_date) / 86400);
        $fine = $days_late * 10; // 10 per day late fee
    }
    
    // Update status to returned
    $conn->query("UPDATE issued_books SET status='returned', fine='$fine' WHERE id='$return_id'");
    
    // Get the book details to update availability
    $book_info = $conn->query("SELECT book_id FROM issued_books WHERE id='$return_id'")->fetch_assoc();
    $conn->query("UPDATE books SET available = available + 1 WHERE id='" . $book_info['book_id'] . "'");
    
    if ($days_late > 0) {
        $success = "✓ Book returned! Late by $days_late day(s). Fine: ₹$fine";
    } else {
        $success = "✓ Book returned successfully on time!";
    }
}

$result = $conn->query("SELECT ib.id, b.title, b.author, b.category, u.name, ib.issue_date, ib.return_date, ib.status, ib.fine
                        FROM issued_books ib
                        JOIN books b ON ib.book_id=b.id
                        JOIN users u ON ib.user_id=u.id
                        ORDER BY ib.issue_date DESC");
?>

<div class="main-content">
    <!-- Page Header -->
    <div class="mb-4">
        <h2><i class="fas fa-list me-2"></i>Issued Books Records</h2>
        <p class="text-muted mb-0">Track all book issues and returns</p>
    </div>

    <!-- Success Message -->
    <?php if ($success): ?>
        <div class="alert alert-success mb-4">
            <i class="fas fa-check-circle me-2"></i><?= $success ?>
        </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <?php
        $statsResult = $conn->query("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status='issued' THEN 1 ELSE 0 END) as currently_issued,
            SUM(CASE WHEN status='returned' THEN 1 ELSE 0 END) as returned
            FROM issued_books");
        $stats = $statsResult->fetch_assoc();
        ?>
        
        <div class="col-md-4 mb-3">
            <div class="stat-card bg-books shadow">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6>Total Issues</h6>
                        <h2><?= $stats['total'] ?></h2>
                        <small class="text-white-50">All time records</small>
                    </div>
                    <div style="font-size: 40px; opacity: 0.8;">📊</div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="stat-card bg-issued shadow">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6>Currently Issued</h6>
                        <h2><?= $stats['currently_issued'] ?? 0 ?></h2>
                        <small class="text-white-50">Books in use</small>
                    </div>
                    <div style="font-size: 40px; opacity: 0.8;">📤</div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="stat-card bg-returned shadow">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6>Returned</h6>
                        <h2><?= $stats['returned'] ?? 0 ?></h2>
                        <small class="text-white-50">Books returned</small>
                    </div>
                    <div style="font-size: 40px; opacity: 0.8;">📥</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter & Search</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" id="bookSearch" class="form-control" placeholder="Search by book title or author...">
                </div>
                <div class="col-md-4">
                    <input type="text" id="userSearch" class="form-control" placeholder="Search by user name...">
                </div>
                <div class="col-md-4">
                    <select id="statusFilter" class="form-control">
                        <option value="">All Status</option>
                        <option value="issued">Currently Issued</option>
                        <option value="returned">Returned</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Issues Table -->
    <div class="card shadow">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-table me-2"></i>Issue History</h5>
        </div>
        <div class="card-body">
            <?php if ($result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="issuesTable">
                        <thead>
                            <tr>
                                <th><i class="fas fa-book me-2"></i>Book Title</th>
                                <th><i class="fas fa-user me-2"></i>User</th>
                                <th><i class="fas fa-calendar me-2"></i>Issue Date</th>
                                <th><i class="fas fa-clock me-2"></i>Return Date</th>
                                <th><i class="fas fa-tag me-2"></i>Status</th>
                                <th><i class="fas fa-money-bill me-2"></i>Fine</th>
                                <th><i class="fas fa-cogs me-2"></i>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): 
                                $return_date = strtotime($row['return_date']);
                                $today = time();
                                $is_overdue = ($today > $return_date && $row['status'] == 'issued');
                            ?>
                                <tr class="issue-row" data-status="<?= $row['status'] ?>" data-book="<?= htmlspecialchars(strtolower($row['title'])) ?>" data-author="<?= htmlspecialchars(strtolower($row['author'])) ?>" data-user="<?= htmlspecialchars(strtolower($row['name'])) ?>">
                                    <td>
                                        <strong><?= htmlspecialchars($row['title']) ?></strong>
                                        <br>
                                        <small class="text-muted">by <?= htmlspecialchars($row['author']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td><?= date('M d, Y', strtotime($row['issue_date'])) ?></td>
                                    <td class="<?= $is_overdue ? 'text-danger' : '' ?>">
                                        <?= date('M d, Y', strtotime($row['return_date'])) ?>
                                        <?php if ($is_overdue): ?>
                                            <br><small class="badge bg-danger">Overdue</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($row['status'] == 'issued'): ?>
                                            <span class="badge bg-warning">
                                                <i class="fas fa-exclamation-circle me-1"></i>Issued
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle me-1"></i>Returned
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($row['fine'] > 0): ?>
                                            <span class="text-danger"><strong>₹<?= $row['fine'] ?></strong></span>
                                        <?php else: ?>
                                            <span class="text-success">₹0</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($row['status'] == 'issued'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="return_id" value="<?= $row['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-success" title="Mark as Returned">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted text-center" style="font-size: 12px;">
                                                <i class="fas fa-check-double"></i> Done
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>No issued books records found</strong>
                    <p class="mb-0 mt-2">Issue books to users to see records here</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.getElementById('bookSearch').addEventListener('keyup', filterTable);
document.getElementById('userSearch').addEventListener('keyup', filterTable);
document.getElementById('statusFilter').addEventListener('change', filterTable);

function filterTable() {
    const bookSearch = document.getElementById('bookSearch').value.toLowerCase();
    const userSearch = document.getElementById('userSearch').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    
    const rows = document.querySelectorAll('.issue-row');
    
    rows.forEach(row => {
        const bookText = row.dataset.book + row.dataset.author;
        const userText = row.dataset.user;
        const status = row.dataset.status;
        
        const matchBook = bookText.includes(bookSearch);
        const matchUser = userText.includes(userSearch);
        const matchStatus = !statusFilter || status === statusFilter;
        
        if (matchBook && matchUser && matchStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>
