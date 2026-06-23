<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Check if admin is logged in
checkAdmin();

// Fetch statistics
$stats = [
    'users' => getRecordCount('users'),
    'products' => getRecordCount('products'),
    'orders' => getRecordCount('orders')
];

function getRecordCount($table) {
    $pdo = getPDO();
    $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
    return $stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="/c2c-ecommerce/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/admin_header.php'; ?>
    
    <div class="admin-container">
        <h1 class="dashboard-title">Dashboard Overview</h1>
        
        <div class="stat-cards">
            <div class="stat-card">
                <h3>Total Users</h3>
                <div class="stat-value"><?= $stats['users'] ?></div>
                <a href="users.php" class="btn">View Users</a>
            </div>
            
            <div class="stat-card">
                <h3>Total Products</h3>
                <div class="stat-value"><?= $stats['products'] ?></div>
                <a href="products.php" class="btn">View Products</a>
            </div>
            
            <div class="stat-card">
                <h3>Total Orders</h3>
                <div class="stat-value"><?= $stats['orders'] ?></div>
                <a href="orders.php" class="btn">View Orders</a>
            </div>
        </div>
        
        <div class="report-section">
            <h2 class="section-title">Generate Reports</h2>
            <form method="post" action="generate_report.php" class="report-form">
                <div class="form-group">
                    <label for="report-type">Report Type:</label>
                    <select id="report-type" name="report_type" class="form-control">
                        <option value="sales">Sales Report</option>
                        <option value="users">User Activity</option>
                        <option value="products">Product Performance</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="date-range">Date Range:</label>
                    <select id="date-range" name="date_range" class="form-control">
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>
                
                <div id="custom-dates" class="custom-date-fields" style="display:none;">
                    <div class="form-group">
                        <label>From:</label>
                        <input type="date" name="start_date" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>To:</label>
                        <input type="date" name="end_date" class="form-control">
                    </div>
                </div>
                
                <input type="hidden" name="format" value="pdf"> <!-- or "csv" -->
                <button type="submit" class="btn btn-primary">Generate Report</button>
            </form>
        </div>
    </div>

    <script>
        // Show/hide custom date fields
        document.getElementById('date-range').addEventListener('change', function() {
            const customDates = document.getElementById('custom-dates');
            customDates.style.display = this.value === 'custom' ? 'block' : 'none';
        });
    </script>
</body>
</html>
