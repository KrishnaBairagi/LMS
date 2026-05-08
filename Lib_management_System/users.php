<?php
session_start();
include 'config/db.php';

// Role-based access control
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

include 'includes/header.php';

$message = "";
$error = "";

// Handle user actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'change_role') {
        $user_id = $_POST['user_id'];
        $new_role = $_POST['new_role'];
        
        if ($new_role === 'admin' || $new_role === 'user') {
            $update = $conn->query("UPDATE users SET role='$new_role' WHERE id='$user_id'");
            if ($update) {
                $message = "✅ User role updated successfully!";
            } else {
                $error = "Error updating role: " . $conn->error;
            }
        }
    }
    else if ($action === 'delete_user') {
        $user_id = $_POST['user_id'];
        
        // Don't allow deleting if they have the only admin account
        $adminCheck = $conn->query("SELECT role FROM users WHERE id='$user_id'")->fetch_assoc();
        
        if ($adminCheck['role'] === 'admin') {
            $adminCount = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='admin'")->fetch_assoc()['count'];
            if ($adminCount <= 1) {
                $error = "Cannot delete the last admin user!";
            } else {
                $delete = $conn->query("DELETE FROM users WHERE id='$user_id'");
                if ($delete) {
                    $message = "✅ User deleted successfully!";
                } else {
                    $error = "Error deleting user: " . $conn->error;
                }
            }
        } else {
            $delete = $conn->query("DELETE FROM users WHERE id='$user_id'");
            if ($delete) {
                $message = "✅ User deleted successfully!";
            } else {
                $error = "Error deleting user: " . $conn->error;
            }
        }
    }
}

// Get all users with issued books count in optimized query
$result = $conn->query("
    SELECT u.id, u.name, u.email, u.role, u.created_at,
           COUNT(CASE WHEN ib.status='issued' THEN 1 END) as issued_books
    FROM users u
    LEFT JOIN issued_books ib ON u.id=ib.user_id
    GROUP BY u.id
    ORDER BY u.created_at DESC
");

// Store users in an array so we can use them twice
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

// Get user statistics
$statsQuery = $conn->query("SELECT 
    COUNT(*) as total_users,
    SUM(CASE WHEN role='admin' THEN 1 ELSE 0 END) as admin_count,
    SUM(CASE WHEN role='user' THEN 1 ELSE 0 END) as user_count
FROM users");
$stats = $statsQuery->fetch_assoc();
?>

<div class="main-content">
    <!-- Page Header -->
    <div class="mb-4">
        <h2><i class="fas fa-users me-2"></i>Manage Users</h2>
        <p class="text-muted mb-0">View and manage all library system users</p>
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

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="stat-card bg-books shadow">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6>Total Users</h6>
                        <h2><?= $stats['total_users'] ?></h2>
                        <small class="text-white-50">Active members</small>
                    </div>
                    <div style="font-size: 40px; opacity: 0.8;">👥</div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="stat-card bg-users shadow">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6>Administrators</h6>
                        <h2><?= $stats['admin_count'] ?></h2>
                        <small class="text-white-50">Admin users</small>
                    </div>
                    <div style="font-size: 40px; opacity: 0.8;">🛡️</div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="stat-card bg-issued shadow">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6>Regular Users</h6>
                        <h2><?= $stats['user_count'] ?></h2>
                        <small class="text-white-50">Library members</small>
                    </div>
                    <div style="font-size: 40px; opacity: 0.8;">👤</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Users Directory</h5>
            <a href="register.php" class="btn btn-primary btn-sm">
                <i class="fas fa-user-plus me-1"></i>Add New User
            </a>
        </div>
        <div class="card-body">
            <?php if (count($users) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="usersTable">
                        <thead>
                            <tr>
                                <th><i class="fas fa-user me-2"></i>Name</th>
                                <th><i class="fas fa-envelope me-2"></i>Email</th>
                                <th><i class="fas fa-shield me-2"></i>Role</th>
                                <th><i class="fas fa-calendar me-2"></i>Joined</th>
                                <th><i class="fas fa-book me-2"></i>Books Issued</th>
                                <th><i class="fas fa-cogs me-2"></i>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #6c84ff, #4969ff); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; margin-right: 10px; font-weight: bold;">
                                                <?= substr($user['name'], 0, 1) ?>
                                            </div>
                                            <strong><?= htmlspecialchars($user['name']) ?></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="mailto:<?= htmlspecialchars($user['email']) ?>" style="color: #6c84ff; text-decoration: none;">
                                            <?= htmlspecialchars($user['email']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php if ($user['role'] == 'admin'): ?>
                                            <span class="badge bg-danger">
                                                <i class="fas fa-shield me-1"></i>Admin
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-info">
                                                <i class="fas fa-user me-1"></i>User
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= date('M d, Y', strtotime($user['created_at'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?= $user['issued_books'] ?? 0 ?> Books</span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <!-- Change Role Button -->
                                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#roleModal<?= $user['id'] ?>" title="Change Role">
                                                <i class="fas fa-exchange-alt"></i>
                                            </button>
                                            
                                            <!-- Delete Button -->
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="delete_user">
                                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                <button type="submit" class="btn btn-danger" title="Delete User" onclick="return confirm('Are you sure you want to delete this user?');">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>No users found</strong>
                    <p class="mb-0 mt-2">
                        <a href="register.php">Create a new user account</a> to get started
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- All Role Change Modals (Outside Table) -->
<?php foreach($users as $user): ?>
    <div class="modal fade" id="roleModal<?= $user['id'] ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-dark">
                <div class="modal-header">
                    <h5 class="modal-title">Change User Role</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="change_role">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        
                        <p><strong><?= htmlspecialchars($user['name']) ?></strong></p>
                        <label class="form-label">New Role:</label>
                        <select name="new_role" class="form-select">
                            <option value="user" <?= ($user['role'] === 'user') ? 'selected' : '' ?>>User</option>
                            <option value="admin" <?= ($user['role'] === 'admin') ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<style>
.table-responsive {
    margin: 0;
}

.badge {
    padding: 6px 12px;
    font-size: 11px;
    font-weight: 600;
}

.btn-group-sm {
    display: flex;
    gap: 5px;
}
</style>

<?php include 'includes/footer.php'; ?>
