<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get real-time statistics
$active_alerts_query = "SELECT COUNT(*) as count FROM alerts WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
$active_alerts_result = mysqli_query($conn, $active_alerts_query);
$active_alerts = mysqli_fetch_assoc($active_alerts_result)['count'];

$critical_alerts_query = "SELECT COUNT(*) as count FROM alerts WHERE level = 'critical' AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
$critical_alerts_result = mysqli_query($conn, $critical_alerts_query);
$critical_alerts = mysqli_fetch_assoc($critical_alerts_result)['count'];

$pending_notifications_query = "SELECT COUNT(*) as count FROM notifications WHERE status = 'pending'";
$pending_notifications_result = mysqli_query($conn, $pending_notifications_query);
$pending_notifications = mysqli_fetch_assoc($pending_notifications_result)['count'];

// Get recent alerts
$recent_alerts_query = "SELECT a.*, u.name as sender_name FROM alerts a 
                       LEFT JOIN users u ON a.sender_id = u.id 
                       ORDER BY a.created_at DESC LIMIT 10";
$recent_alerts_result = mysqli_query($conn, $recent_alerts_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>911 Dispatch Center - Emergency Communication System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="dashboard-page dispatch-center">
    <nav class="navbar dispatch-navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <span class="brand-icon">🚨</span>
                <span class="brand-text">911 DISPATCH CENTER</span>
                <span class="status-indicator active" id="systemStatus">
                    <span class="status-dot"></span>
                    SYSTEM OPERATIONAL
                </span>
            </div>
            <div class="nav-user">
                <span class="dispatcher-id">Dispatcher: <?php echo htmlspecialchars(strtoupper($_SESSION['user_name'])); ?></span>
                <span class="current-time" id="currentTime"></span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </nav>

    <div class="dispatch-container">
        <!-- Status Bar -->
        <div class="status-bar">
            <div class="status-item">
                <span class="status-label">Active Alerts</span>
                <span class="status-value alert-count"><?php echo $active_alerts; ?></span>
            </div>
            <div class="status-item critical">
                <span class="status-label">Critical</span>
                <span class="status-value critical-count"><?php echo $critical_alerts; ?></span>
            </div>
            <div class="status-item">
                <span class="status-label">Pending Notifications</span>
                <span class="status-value"><?php echo $pending_notifications; ?></span>
            </div>
            <div class="status-item">
                <span class="status-label">Response Time</span>
                <span class="status-value">2.3 min</span>
            </div>
            <div class="status-item">
                <span class="status-label">Units Available</span>
                <span class="status-value">12/15</span>
            </div>
        </div>

        <!-- Main Dashboard Grid -->
        <div class="dispatch-grid">
            <!-- Active Incidents Panel -->
            <div class="dispatch-panel active-incidents">
                <div class="panel-header">
                    <h2>ACTIVE INCIDENTS</h2>
                    <span class="panel-badge"><?php echo $active_alerts; ?></span>
                </div>
                <div class="incidents-list">
                    <?php if (mysqli_num_rows($recent_alerts_result) > 0): ?>
                        <?php while ($alert = mysqli_fetch_assoc($recent_alerts_result)): ?>
                            <div class="incident-item level-<?php echo $alert['level']; ?>">
                                <div class="incident-priority">
                                    <span class="priority-indicator"></span>
                                </div>
                                <div class="incident-content">
                                    <div class="incident-header">
                                        <span class="incident-id">#<?php echo $alert['id']; ?></span>
                                        <span class="incident-level"><?php echo strtoupper($alert['level']); ?></span>
                                    </div>
                                    <div class="incident-title"><?php echo htmlspecialchars($alert['title'] ?: 'Emergency Alert'); ?></div>
                                    <div class="incident-meta">
                                        <span class="incident-source"><?php echo strtoupper($alert['source']); ?></span>
                                        <span class="incident-time"><?php echo date('H:i:s', strtotime($alert['created_at'])); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-incidents">No active incidents</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Response Units Panel -->
            <div class="dispatch-panel response-units">
                <div class="panel-header">
                    <h2>RESPONSE UNITS</h2>
                </div>
                <div class="units-grid">
                    <div class="unit-card available">
                        <div class="unit-id">UNIT-01</div>
                        <div class="unit-status">AVAILABLE</div>
                        <div class="unit-type">Fire & Rescue</div>
                    </div>
                    <div class="unit-card available">
                        <div class="unit-id">UNIT-02</div>
                        <div class="unit-status">AVAILABLE</div>
                        <div class="unit-type">Medical</div>
                    </div>
                    <div class="unit-card dispatched">
                        <div class="unit-id">UNIT-03</div>
                        <div class="unit-status">DISPATCHED</div>
                        <div class="unit-type">Police</div>
                    </div>
                    <div class="unit-card available">
                        <div class="unit-id">UNIT-04</div>
                        <div class="unit-status">AVAILABLE</div>
                        <div class="unit-type">Hazmat</div>
                    </div>
                </div>
            </div>

            <!-- System Status Panel -->
            <div class="dispatch-panel system-status">
                <div class="panel-header">
                    <h2>SYSTEM STATUS</h2>
                </div>
                <div class="system-metrics">
                    <div class="metric-item">
                        <span class="metric-label">Communication</span>
                        <div class="metric-bar">
                            <div class="metric-fill" style="width: 98%"></div>
                        </div>
                        <span class="metric-value">98%</span>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">Database</span>
                        <div class="metric-bar">
                            <div class="metric-fill" style="width: 100%"></div>
                        </div>
                        <span class="metric-value">100%</span>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">Network</span>
                        <div class="metric-bar">
                            <div class="metric-fill" style="width: 95%"></div>
                        </div>
                        <span class="metric-value">95%</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Panel -->
            <div class="dispatch-panel quick-actions-panel">
                <div class="panel-header">
                    <h2>QUICK ACTIONS</h2>
                </div>
                <div class="action-buttons">
                    <button class="dispatch-btn primary">
                        <span class="btn-icon">📞</span>
                        <span>NEW CALL</span>
                    </button>
                    <button class="dispatch-btn warning">
                        <span class="btn-icon">🚨</span>
                        <span>SEND ALERT</span>
                    </button>
                    <button class="dispatch-btn info">
                        <span class="btn-icon">📋</span>
                        <span>REPORTS</span>
                    </button>
                    <button class="dispatch-btn">
                        <span class="btn-icon">⚙️</span>
                        <span>SETTINGS</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Update time
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { hour12: false });
            document.getElementById('currentTime').textContent = timeString;
        }
        updateTime();
        setInterval(updateTime, 1000);

        // Blinking effect for critical alerts
        const criticalItems = document.querySelectorAll('.level-critical');
        setInterval(() => {
            criticalItems.forEach(item => {
                item.classList.toggle('blink');
            });
        }, 1000);
    </script>
</body>
</html>
