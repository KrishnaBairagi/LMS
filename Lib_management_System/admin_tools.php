<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

include 'includes/header.php';
?>

<div class="main-content">
    <!-- Page Header -->
    <div class="mb-4">
        <h2><i class="fas fa-toolbox me-2"></i>System Tools & Diagnostics</h2>
        <p class="text-muted mb-0">Admin only - System maintenance and testing utilities</p>
    </div>

    <!-- Diagnostic Tools -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card shadow">
                <div class="card-header bg-primary">
                    <h5 class="mb-0"><i class="fas fa-database me-2"></i>System Verification</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Verify database tables, columns, and integrity.</p>
                    <a href="system_verify.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-check me-1"></i>Run Verification
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card shadow">
                <div class="card-header bg-info">
                    <h5 class="mb-0"><i class="fas fa-microscope me-2"></i>Full Diagnostic</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Inspect complete system state, session, and database records.</p>
                    <a href="full_diagnostic.php" class="btn btn-info btn-sm">
                        <i class="fas fa-eye me-1"></i>View Diagnostic
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card shadow">
                <div class="card-header bg-success">
                    <h5 class="mb-0"><i class="fas fa-flask me-2"></i>Test Workflow</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Test the complete approval workflow with sample data.</p>
                    <a href="test_approval_workflow.php" class="btn btn-success btn-sm">
                        <i class="fas fa-play me-1"></i>Run Test
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card shadow">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="fas fa-bug me-2"></i>Debug Requests</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Inspect book requests for current user (requires session).</p>
                    <a href="debug_requests.php" class="btn btn-warning btn-sm">
                        <i class="fas fa-search me-1"></i>Debug
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card shadow">
                <div class="card-header bg-danger">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Troubleshoot Requests</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Diagnose and fix book request submission issues.</p>
                    <a href="troubleshoot_requests.php" class="btn btn-danger btn-sm">
                        <i class="fas fa-wrench me-1"></i>Troubleshoot
                    </a>
                </div>
            </div>
        </div>
    <div class="card shadow">
        <div class="card-header bg-secondary">
            <h5 class="mb-0"><i class="fas fa-book me-2"></i>System Documentation</h5>
        </div>
        <div class="card-body">
            <h6>Recent Fixes & Improvements:</h6>
            <ul>
                <li><strong>Book Return</strong> - Fixed availability increment when books are returned</li>
                <li><strong>Approval Workflow</strong> - Improved logic to prevent partial failures</li>
                <li><strong>Issue Book</strong> - Added user selection dropdown for admins</li>
                <li><strong>Type Safety</strong> - Applied proper intval() casting to all IDs</li>
                <li><strong>Error Handling</strong> - Enhanced error messages and debugging</li>
            </ul>

            <h6 class="mt-3">Complete Workflow:</h6>
            <ol>
                <li><strong>User requests book</strong> - Request Book page → "Request Book" button</li>
                <li><strong>Admin approves</strong> - Book Requests page → approve pending requests</li>
                <li><strong>User sees book</strong> - My Books & Penalties page → shows issued books</li>
                <li><strong>User returns book</strong> - Click "Return" button to complete</li>
            </ol>

            <p class="mt-3"><a href="SYSTEM_FIXES_SUMMARY.md" class="btn btn-outline-primary">
                <i class="fas fa-file-alt me-1"></i>View Full Summary (Markdown)
            </a></p>
        </div>
    </div>

    <!-- Quick Commands -->
    <div class="card shadow mt-4" style="background-color: #252d3d; border: 1px solid #4969ff;">
        <div class="card-header" style="background-color: #4969ff;">
            <h5 class="mb-0"><i class="fas fa-terminal me-2"></i>Admin Quick Links</h5>
        </div>
        <div class="card-body">
            <div class="btn-group-vertical w-100">
                <a href="manage_requests.php" class="btn btn-outline-primary text-start">
                    <i class="fas fa-tasks me-2"></i>Go to Book Requests
                </a>
                <a href="issued_books.php" class="btn btn-outline-primary text-start">
                    <i class="fas fa-list me-2"></i>Go to Issued Books
                </a>
                <a href="users.php" class="btn btn-outline-primary text-start">
                    <i class="fas fa-users me-2"></i>Go to Users Management
                </a>
                <a href="analytics.php" class="btn btn-outline-primary text-start">
                    <i class="fas fa-chart-bar me-2"></i>Go to Analytics
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
