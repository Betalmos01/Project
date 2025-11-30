<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Emergency Communication System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="dashboard-page">
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <span class="brand-icon">🚨</span>
                <span class="brand-text">Emergency Comm</span>
            </div>
            <div class="nav-user">
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="welcome-section">
            <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! 👋</h1>
            <p class="welcome-subtitle">Here's your dashboard overview</p>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-card role-card">
                <div class="card-icon">👤</div>
                <div class="card-content">
                    <h3>Your Role</h3>
                    <p class="card-value"><?php echo htmlspecialchars(ucfirst($_SESSION['user_role'])); ?></p>
                </div>
            </div>

            <div class="dashboard-card stats-card">
                <div class="card-icon">📊</div>
                <div class="card-content">
                    <h3>System Status</h3>
                    <p class="card-value">Active</p>
                </div>
            </div>

            <div class="dashboard-card info-card">
                <div class="card-icon">ℹ️</div>
                <div class="card-content">
                    <h3>User ID</h3>
                    <p class="card-value">#<?php echo htmlspecialchars($_SESSION['user_id']); ?></p>
                </div>
            </div>
        </div>

        <div class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="actions-grid">
                <button class="action-btn">
                    <span class="action-icon">📢</span>
                    <span>Send Alert</span>
                </button>
                <button class="action-btn">
                    <span class="action-icon">📋</span>
                    <span>View Reports</span>
                </button>
                <button class="action-btn">
                    <span class="action-icon">⚙️</span>
                    <span>Settings</span>
                </button>
                <button class="action-btn">
                    <span class="action-icon">📞</span>
                    <span>Contacts</span>
                </button>
            </div>
        </div>
    </div>
</body>
</html>
