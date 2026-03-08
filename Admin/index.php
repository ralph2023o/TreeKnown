3
<?php
// dashboard.php

// Example placeholders for dynamic data
$totalUsers = 120;   // Placeholder for total users
$totalSales = 4500;  // Placeholder for total sales
$pendingOrders = 15; // Placeholder for pending orders
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            color: white;
        }
        .sidebar a {
            color: white;
            display: block;
            padding: 10px;
            text-decoration: none;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .card {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 sidebar p-3">
            <h4>Admin Panel</h4>
            <a href="#">Dashboard</a>
            <a href="#">Users</a>
            <a href="#">Orders</a>
            <a href="#">Products</a>
            <a href="#">Settings</a>
        </div>

        <!-- Main Content -->
        <div class="col-md-10 p-4">
            <h2>Dashboard</h2>

            <!-- Stats Cards -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card text-bg-primary p-3">
                        <h5>Total Users</h5>
                        <h2><?php echo $totalUsers; ?></h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-bg-success p-3">
                        <h5>Total Sales</h5>
                        <h2><?php echo $totalSales; ?></h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-bg-warning p-3">
                        <h5>Pending Orders</h5>
                        <h2><?php echo $pendingOrders; ?></h2>
                    </div>
                </div>
            </div>

            <!-- Placeholder Table -->
            <div class="card mt-4 p-3">
                <h5>Recent Users</h5>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>John Doe</td>
                            <td>john@example.com</td>
                            <td>Admin</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Jane Smith</td>
                            <td>jane@example.com</td>
                            <td>User</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Bob Johnson</td>
                            <td>bob@example.com</td>
                            <td>User</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Placeholder Chart -->
            <div class="card mt-4 p-3">
                <h5>Sales Overview</h5>
                <canvas id="salesChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- JS Libraries -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
            datasets: [{
                label: 'Sales',
                data: [1200, 1900, 3000, 2500, 4000],
                borderColor: 'rgba(75, 192, 192, 1)',
                fill: false,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
        }
    });
</script>
</body>
</html>