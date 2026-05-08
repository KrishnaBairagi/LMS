<?php
session_start();
include 'config/db.php';
include 'includes/header.php';

// Only allow admins to create admin users OR first-time setup
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$user_count = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
$admin_count = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='admin'")->fetch_assoc()['total'];
$is_first_setup = ($user_count === 0 || $admin_count === 0);

if (!$is_admin && !$is_first_setup) {
    header('Location: dashboard.php');
    exit();
}

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create_admin') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm = $_POST['confirm'];
        
        if (empty($name) || empty($email) || empty($password)) {
            $error = "All fields are required";
        } else if ($password !== $confirm) {
            $error = "Passwords do not match";
        } else if (strlen($password) < 6) {
            $error = "Password must be at least 6 characters";
        } else {
            // Check if email already exists
            $check = $conn->query("SELECT id FROM users WHERE email='$email'");
            if ($check->num_rows > 0) {
                $error = "Email already exists";
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $insert = $conn->query("INSERT INTO users(name,email,password,role) VALUES('$name','$email','$passwordHash','admin')");
                
                if ($insert) {
                    $message = "✅ Admin user created successfully!";
                } else {
                    $error = "Error creating user: " . $conn->error;
                }
            }
        }
    } 
    else if ($action === 'change_role') {
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
}

// Get all users
$users_result = $conn->query("SELECT id, name, email, role FROM users ORDER BY created_at DESC");
?>

<div class="main-content">
    <!-- Page Header -->
    <div class="mb-4">
        <h2><i class="fas fa-shield-alt me-2"></i>Admin Management</h2>
        <p class="text-muted mb-0">Create and manage admin users</p>
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

    <!-- Create Admin Form -->
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Create New Admin User</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="action" value="create_admin">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-user me-2"></i>Full Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Enter full name" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-envelope me-2"></i>Email</label>
                        <input type="email" name="email" class="form-control" placeholder="Enter email" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-lock me-2"></i>Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Minimum 6 characters" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-lock me-2"></i>Confirm Password</label>
                        <input type="password" name="confirm" class="form-control" placeholder="Confirm password" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Create Admin</button>
            </form>
        </div>
    </div>

    <!-- User Roles Management -->
    <?php if ($is_admin): ?>
    <div class="card shadow">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-users-cog me-2"></i>Manage User Roles</h5>
        </div>
        <div class="card-body">
            <?php if ($users_result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th><i class="fas fa-user me-2"></i>Name</th>
                                <th><i class="fas fa-envelope me-2"></i>Email</th>
                                <th><i class="fas fa-shield me-2"></i>Current Role</th>
                                <th><i class="fas fa-cogs me-2"></i>Change Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($user = $users_result->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($user['name']) ?></strong></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td>
                                        <?php if ($user['role'] === 'admin'): ?>
                                            <span class="badge bg-danger"><i class="fas fa-shield me-1"></i>Admin</span>
                                        <?php else: ?>
                                            <span class="badge bg-info"><i class="fas fa-user me-1"></i>User</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="change_role">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            
                                            <?php if ($user['role'] === 'admin'): ?>
                                                <input type="hidden" name="new_role" value="user">
                                                <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Demote to regular user?');">
                                                    <i class="fas fa-arrow-down me-1"></i>Demote
                                                </button>
                                            <?php else: ?>
                                                <input type="hidden" name="new_role" value="admin">
                                                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Promote to admin?');">
                                                    <i class="fas fa-arrow-up me-1"></i>Promote
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No users found</div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
