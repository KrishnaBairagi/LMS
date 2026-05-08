<?php
session_start();
include 'config/db.php';

// Role-based access control
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

$title=$_POST['title'];
$author=$_POST['author'];
$category=$_POST['category'];
$quantity=$_POST['quantity'];
$image=$_POST['image'];  // Changed from file upload to URL

$conn->query("INSERT INTO books(title,author,category,quantity,available,image)
VALUES('$title','$author','$category','$quantity','$quantity','$image')");

header("Location: books.php");
}

include 'includes/header.php';
?>

<div class="main-content">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-plus-circle me-2"></i> Add New Book</h4>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><i class="fas fa-book me-2"></i>Book Title</label>
                                    <input type="text" name="title" class="form-control" placeholder="Enter book title" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><i class="fas fa-user me-2"></i>Author Name</label>
                                    <input type="text" name="author" class="form-control" placeholder="Enter author name" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><i class="fas fa-tag me-2"></i>Category</label>
                                    <input type="text" name="category" class="form-control" placeholder="e.g., Science, Fiction, History" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><i class="fas fa-boxes me-2"></i>Quantity</label>
                                    <input type="number" name="quantity" class="form-control" placeholder="Number of copies" min="1" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-image me-2"></i>Book Cover Image URL</label>
                            <input type="url" name="image" class="form-control" placeholder="https://example.com/image.jpg" required>
                            <small class="text-muted">Paste the full URL of the book cover image (e.g., https://covers.openlibrary.org/b/id/...jpg)</small>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check me-2"></i> Add Book
                            </button>
                            <a href="books.php" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
