<?php
session_start();
include 'config/db.php';

// Role-based access control
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $book_id = intval($_POST['book_id']);
    $user_id = intval($_POST['user_id']);
    $issue_date = date("Y-m-d");
    $return_date = date("Y-m-d", strtotime("+7 days"));

    // Validate user exists and is not an admin
    $userCheck = $conn->query("SELECT id, name, role FROM users WHERE id='$user_id'")->fetch_assoc();
    
    if (!$userCheck) {
        $error = "✕ Invalid user selected!";
    } else if ($userCheck['role'] === 'admin') {
        $error = "✕ Cannot issue books to admin users!";
    } else {
        // Check if book is available
        $bookCheck = $conn->query("SELECT available FROM books WHERE id='$book_id'")->fetch_assoc();
        
        if ($bookCheck['available'] > 0) {
            $conn->query("INSERT INTO issued_books (user_id,book_id,issue_date,return_date)
                          VALUES ('$user_id','$book_id','$issue_date','$return_date')");

            $conn->query("UPDATE books SET available = available - 1 WHERE id='$book_id'");
            
            // Get book details for success message
            $bookDetails = $conn->query("SELECT title FROM books WHERE id='$book_id'")->fetch_assoc();
            $success = "✓ Book '" . htmlspecialchars($bookDetails['title']) . "' issued to " . htmlspecialchars($userCheck['name']) . "! Return date: " . date("M d, Y", strtotime($return_date));
        } else {
            $error = "✕ This book is no longer available!";
        }
    }
}

$result = $conn->query("SELECT * FROM books WHERE available > 0 ORDER BY title");
include 'includes/header.php';
?>

<div class="main-content">
    <!-- Page Header -->
    <div class="mb-4">
        <h2><i class="fas fa-arrow-right me-2"></i>Issue Book to User</h2>
        <p class="text-muted mb-0">Select a book from the available inventory and issue it</p>
    </div>

    <!-- Success/Error Messages -->
    <?php if ($success): ?>
        <div class="alert alert-success mb-4">
            <i class="fas fa-check-circle me-2"></i><?= $success ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger mb-4">
            <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
        </div>
    <?php endif; ?>

    <!-- Tabs Section -->
    <div class="row">
        <div class="col-lg-8">
            <!-- Search/Filter Section -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-search me-2"></i>Available Books</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" id="bookSearch" class="form-control" placeholder="Search by title or author...">
                        </div>
                        <div class="col-md-6">
                            <select id="categoryFilter" class="form-control">
                                <option value="">All Categories</option>
                                <?php 
                                $categories = $conn->query("SELECT DISTINCT category FROM books WHERE available > 0 ORDER BY category");
                                while($cat = $categories->fetch_assoc()): 
                                ?>
                                    <option value="<?= htmlspecialchars($cat['category']) ?>"><?= htmlspecialchars($cat['category']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Books List -->
            <div id="booksContainer">
                <?php if ($result->num_rows > 0): ?>
                    <div class="row">
                        <?php while($book = $result->fetch_assoc()): ?>
                            <div class="col-md-6 mb-4 book-item">
                                <div class="card book-selection-card shadow">
                                    <div class="book-selection-image">
                                        <?php if (!empty($book['image'])): ?>
                                            <img src="<?= htmlspecialchars($book['image']) ?>" alt="<?= htmlspecialchars($book['title']) ?>" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%25%22 height=%22100%25%22%3E%3Crect fill=%22%23ddd%22 width=%22100%25%22 height=%22100%25%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-family=%22Arial%22 font-size=%2224%22 fill=%22%23999%22%3E📖%3C/text%3E%3C/svg%3E'">
                                        <?php else: ?>
                                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 80px;">📖</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="card-title"><?= htmlspecialchars($book['title']) ?></h6>
                                        <p class="card-text text-muted" style="font-size: 13px; margin-bottom: 10px;">
                                            by <?= htmlspecialchars($book['author']) ?>
                                        </p>
                                        <div class="mb-2">
                                            <span class="badge bg-primary"><?= htmlspecialchars($book['category']) ?></span>
                                        </div>
                                        <div class="availability-info mb-3">
                                            <small class="text-muted">
                                                <i class="fas fa-boxes me-1"></i>Available: <strong><?= $book['available'] ?></strong> / <strong><?= $book['quantity'] ?></strong>
                                            </small>
                                        </div>
                                        <form method="POST" class="issue-form">
                                            <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                                            <input type="hidden" name="user_id" id="userIdField" value="">
                                            <button type="submit" class="btn btn-success btn-sm w-100">
                                                <i class="fas fa-arrow-right me-1"></i>Issue This Book
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>No books available at the moment</strong>
                        <p class="mb-0 mt-2">Please check back later or add new books to the inventory</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Info Panel -->
        <div class="col-lg-4">
            <!-- User Selection Card -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user-check me-2"></i>Select User</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="userSelect" class="form-label text-muted">
                            <i class="fas fa-users me-2"></i>Issue To:
                        </label>
                        <select id="userSelect" name="user_id" class="form-control" required>
                            <option value="">-- Select a User --</option>
                            <?php 
                            $users = $conn->query("SELECT id, name, email FROM users WHERE role='user' ORDER BY name");
                            while($user = $users->fetch_assoc()): 
                            ?>
                                <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['email']) ?>)</option>
                            <?php endwhile; ?>
                        </select>
                        <small class="text-muted d-block mt-2">
                            <i class="fas fa-info-circle me-1"></i>Only regular users appear here
                        </small>
                    </div>
                </div>
            </div>

            <!-- Issue Info Card -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Issue Information</h5>
                </div>
                <div class="card-body">
                    <div class="info-item mb-3">
                        <label class="text-muted"><i class="fas fa-user me-2"></i>Issued By:</label>
                        <p class="text-primary"><strong><?= htmlspecialchars($_SESSION['user'] ?? 'Admin') ?></strong></p>
                    </div>
                    <hr>
                    <div class="info-item mb-3">
                        <label class="text-muted"><i class="fas fa-calendar me-2"></i>Issue Duration:</label>
                        <p><strong>7 Days</strong></p>
                    </div>
                    <hr>
                    <div class="info-item">
                        <label class="text-muted"><i class="fas fa-clock me-2"></i>Return Date:</label>
                        <p><strong><?= date("M d, Y", strtotime("+7 days")) ?></strong></p>
                    </div>
                </div>
            </div>

            <!-- Instructions Card -->
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-book me-2"></i>Issuing Instructions</h5>
                </div>
                <div class="card-body">
                    <ol class="small">
                        <li class="mb-2">
                            <strong>Select a Book</strong>
                            <p class="text-muted mb-0">Choose from the available books list</p>
                        </li>
                        <li class="mb-2">
                            <strong>Click Issue Button</strong>
                            <p class="text-muted mb-0">Complete the issuance process</p>
                        </li>
                        <li class="mb-2">
                            <strong>Book is Issued</strong>
                            <p class="text-muted mb-0">User can now access the book</p>
                        </li>
                        <li>
                            <strong>Return Within 7 Days</strong>
                            <p class="text-muted mb-0">Book must be returned by the due date</p>
                        </li>
                    </ol>
                    <div class="alert alert-warning mt-3 mb-0">
                        <small>
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Late returns may incur fine charges
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Styles for this page -->
<style>
.book-selection-card {
    transition: all 0.3s ease;
    border: 2px solid transparent;
    cursor: pointer;
}

.book-selection-card:hover {
    border-color: var(--primary);
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(108, 132, 255, 0.2) !important;
}

.book-selection-image {
    width: 100%;
    height: 200px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px 12px 0 0;
}

.book-selection-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.book-item.hidden {
    display: none;
}

.availability-info {
    background: rgba(45, 212, 191, 0.1);
    padding: 8px;
    border-radius: 6px;
    border-left: 3px solid #2dd4bf;
}

.info-item label {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}

.info-item p {
    margin: 5px 0 0 0;
    font-size: 14px;
}

.issue-form {
    margin-top: auto;
}

@media (max-width: 768px) {
    .col-lg-4 {
        margin-top: 20px;
    }
}
</style>

<script>
document.getElementById('bookSearch').addEventListener('keyup', filterBooks);
document.getElementById('categoryFilter').addEventListener('change', filterBooks);
document.getElementById('userSelect').addEventListener('change', updateUserField);

// Sync user dropdown with hidden input fields
function updateUserField() {
    const userSelect = document.getElementById('userSelect');
    const userIdFields = document.querySelectorAll('[id="userIdField"]');
    const userId = userSelect.value;
    
    userIdFields.forEach(field => {
        field.value = userId;
    });
}

// Validate form before submit
document.querySelectorAll('.issue-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const userSelect = document.getElementById('userSelect').value;
        if (!userSelect) {
            e.preventDefault();
            alert('⚠️ Please select a user first!');
            document.getElementById('userSelect').focus();
        }
    });
});

function filterBooks() {
    const searchTerm = document.getElementById('bookSearch').value.toLowerCase();
    const categoryFilter = document.getElementById('categoryFilter').value.toLowerCase();
    const bookItems = document.querySelectorAll('.book-item');

    bookItems.forEach(item => {
        const title = item.querySelector('.card-title').textContent.toLowerCase();
        const author = item.querySelector('.card-text').textContent.toLowerCase();
        const category = item.querySelector('.badge').textContent.toLowerCase();

        const matchSearch = title.includes(searchTerm) || author.includes(searchTerm);
        const matchCategory = !categoryFilter || category.includes(categoryFilter);

        if (matchSearch && matchCategory) {
            item.classList.remove('hidden');
        } else {
            item.classList.add('hidden');
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>
