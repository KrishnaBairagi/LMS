<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>📚 Library Management System</title>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Syne:wght@700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="assets/css/style.css">

<style>
    /* Top Navigation Bar */
    .navbar-top {
        background: linear-gradient(90deg, rgba(15, 23, 42, 0.95), rgba(30, 41, 59, 0.95));
        backdrop-filter: blur(10px);
        border-bottom: 1px solid rgba(99, 102, 241, 0.1);
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        height: 70px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 40px;
        z-index: 999;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    }

    .navbar-brand-top {
        display: flex;
        align-items: center;
        gap: 15px;
        text-decoration: none;
        color: #f1f5f9;
        font-size: 24px;
        font-weight: 700;
        font-family: 'Poppins', sans-serif;
    }

    .navbar-brand-top span {
        color: #6366f1;
    }

    .navbar-user {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .user-profile {
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        padding: 8px 12px;
        border-radius: 10px;
        transition: all 0.3s;
    }

    .user-profile:hover {
        background: rgba(99, 102, 241, 0.1);
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 16px;
    }

    .user-info {
        text-align: right;
    }

    .user-name {
        color: #f1f5f9;
        font-weight: 600;
        font-size: 13px;
    }

    .user-role {
        color: #94a3b8;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .nav-divider {
        width: 1px;
        height: 40px;
        background: rgba(99, 102, 241, 0.2);
    }

    .sidebar {
        margin-top: 70px;
    }

    .main {
        margin-top: 70px;
    }
</style>

</head>
<body>

<!-- Top Navigation Bar -->
<div class="navbar-top">
    <div class="navbar-brand-top">
        <i class="fas fa-book-open"></i>
        <span>Library</span> Pro
    </div>
    
    <div class="navbar-user">
        <?php if(isset($_SESSION['user'])): ?>
            <div class="user-profile">
                <div class="user-avatar">
                    <?= strtoupper(substr($_SESSION['user'], 0, 1)) ?>
                </div>
                <div class="user-info">
                    <div class="user-name"><?= htmlspecialchars($_SESSION['user']) ?></div>
                    <div class="user-role"><?= $_SESSION['role'] ?? 'user' ?></div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="sidebar">
    <h4 class="text-center py-3">📚 Library</h4>

    <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
    <a href="books.php"><i class="fas fa-book"></i> Books</a>
    
    <!-- Admin Only Links -->
    <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <a href="manage_requests.php"><i class="fas fa-tasks"></i> Book Requests</a>
        <a href="add_book.php"><i class="fas fa-plus"></i> Add Book</a>
        <a href="issued_books.php"><i class="fas fa-list"></i> Issued Books</a>
        <a href="analytics.php"><i class="fas fa-chart-bar"></i> Analytics</a>
        <a href="users.php"><i class="fas fa-users"></i> Users</a>
        <hr style="opacity: 0.2;">
    <?php else: ?>
    <!-- User Links -->
        <a href="request_book.php"><i class="fas fa-paper-plane"></i> Request Book</a>
        <a href="my_requests.php"><i class="fas fa-tasks"></i> My Requests</a>
        <a href="issued_book.php"><i class="fas fa-book-open"></i> My Books & Penalties</a>
        <a href="search.php"><i class="fas fa-search"></i> Search Books</a>
        <hr style="opacity: 0.2;">
    <?php endif; ?>
    
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="main">
