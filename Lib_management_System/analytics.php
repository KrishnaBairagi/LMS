<?php
session_start();
include 'config/db.php';

// Role-based access control
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

include 'includes/header.php';
?>

<h4>Analytics</h4>

<canvas id="chart"></canvas>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const ctx = document.getElementById('chart');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Books','Users','Issued'],
        datasets: [{
            label: 'Library Stats',
            data: [10,5,7]
        }]
    }
});
</script>

<?php include 'includes/footer.php'; ?>
