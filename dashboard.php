<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: login.html");
    exit();
}

$user_email = $_SESSION["email"];
$user_id = $_SESSION["user_id"];

// Include database connection to get user stats
require_once 'api/auth/db.php';

// Get user registration date
try {
    $getUserInfo = $pdo->prepare("SELECT created_at FROM users WHERE id = ?");
    $getUserInfo->execute([$user_id]);
    $userInfo = $getUserInfo->fetch();
    $registration_date = $userInfo ? $userInfo['created_at'] : 'Unknown';
    
    // Get total users count
    $totalUsersQuery = $pdo->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $totalUsersQuery->fetch()['total'];
    
} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $registration_date = 'Unknown';
    $totalUsers = 0;
}

// Check if user is admin (user_id = 1 or specific email)
$isAdmin = ($user_id == 1 || $user_email == 'admin@example.com');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Supabase Clone</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
            min-height: 100vh;
        }
        
        .header {
            background: white;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
        }
        
        .nav-links {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        .nav-link {
            color: #666;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 6px;
            transition: all 0.3s;
        }
        
        .nav-link:hover {
            background: #f1f5f9;
            color: #667eea;
        }
        
        .admin-link {
            background: #667eea;
            color: white !important;
        }
        
        .admin-link:hover {
            background: #5a67d8;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-email {
            color: #666;
            font-size: 14px;
        }
        
        .logout-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
            text-decoration: none;
        }
        
        .logout-btn:hover {
            background: #c82333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .welcome-card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            text-align: center;
        }
        
        .welcome-title {
            font-size: 32px;
            color: #333;
            margin-bottom: 10px;
        }
        
        .welcome-subtitle {
            color: #666;
            font-size: 18px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .action-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .action-title {
            font-size: 20px;
            color: #333;
            margin-bottom: 15px;
        }
        
        .action-description {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .action-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: transform 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
        }
        
        .session-info {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .session-title {
            font-size: 20px;
            color: #333;
            margin-bottom: 20px;
        }
        
        .session-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .session-item {
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
        }
        
        .session-key {
            color: #667eea;
            font-weight: bold;
        }
        
        .session-value {
            color: #333;
        }
        
        .data-table {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .table-header {
            background: #667eea;
            color: white;
            padding: 20px;
            font-size: 18px;
            font-weight: 600;
        }
        
        .table-content {
            padding: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        
        th {
            background: #f8fafc;
            font-weight: 600;
            color: #334155;
        }
        
        tr:hover {
            background: #f8fafc;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-active {
            background: #dcfce7;
            color: #166534;
        }
        
        .status-inactive {
            background: #fef2f2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">Supabase Clone</div>
        <div class="nav-links">
            <a href="dashboard.php" class="nav-link">Dashboard</a>
            <?php if ($isAdmin): ?>
                <a href="admin.php" class="nav-link admin-link">Admin Panel</a>
            <?php endif; ?>
        </div>
        <div class="user-info">
            <span class="user-email"><?php echo htmlspecialchars($user_email); ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="welcome-card">
            <h1 class="welcome-title">Welcome, <?php echo htmlspecialchars($user_email); ?>!</h1>
            <p class="welcome-subtitle">You have successfully logged into your dashboard</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">1</div>
                <div class="stat-label">Active Sessions</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalUsers; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo date('H:i'); ?></div>
                <div class="stat-label">Current Time</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo date('M d'); ?></div>
                <div class="stat-label">Today's Date</div>
            </div>
        </div>

        <div class="actions-grid">
            <div class="action-card">
                <h3 class="action-title">Profile Settings</h3>
                <p class="action-description">Update your profile information, change password, and manage account settings.</p>
                <button class="action-btn" onclick="alert('Profile settings coming soon!')">Manage Profile</button>
            </div>
            <div class="action-card">
                <h3 class="action-title">API Keys</h3>
                <p class="action-description">Generate and manage API keys for your applications and integrations.</p>
                <button class="action-btn" onclick="alert('API keys coming soon!')">View API Keys</button>
            </div>
            <div class="action-card">
                <h3 class="action-title">Database</h3>
                <p class="action-description">Access your database tables, run queries, and manage your data.</p>
                <button class="action-btn" onclick="alert('Database access coming soon!')">Open Database</button>
            </div>
            <div class="action-card">
                <h3 class="action-title">Analytics</h3>
                <p class="action-description">View usage statistics, performance metrics, and user analytics.</p>
                <button class="action-btn" onclick="alert('Analytics coming soon!')">View Analytics</button>
            </div>
        </div>

        <div class="data-table">
            <div class="table-header">Recent Activity</div>
            <div class="table-content">
                <table>
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Status</th>
                            <th>Time</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>User Login</td>
                            <td><span class="status-badge status-active">Success</span></td>
                            <td><?php echo date('Y-m-d H:i:s'); ?></td>
                            <td><?php echo $_SERVER['REMOTE_ADDR']; ?></td>
                        </tr>
                        <tr>
                            <td>Session Started</td>
                            <td><span class="status-badge status-active">Active</span></td>
                            <td><?php echo date('Y-m-d H:i:s'); ?></td>
                            <td><?php echo $_SERVER['REMOTE_ADDR']; ?></td>
                        </tr>
                        <tr>
                            <td>Dashboard Access</td>
                            <td><span class="status-badge status-active">Success</span></td>
                            <td><?php echo date('Y-m-d H:i:s'); ?></td>
                            <td><?php echo $_SERVER['REMOTE_ADDR']; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="session-info">
            <h2 class="session-title">Session Information</h2>
            <div class="session-details">
                <div class="session-item">
                    <span class="session-key">User ID:</span>
                    <span class="session-value"><?php echo htmlspecialchars($user_id); ?></span>
                </div>
                <div class="session-item">
                    <span class="session-key">Email:</span>
                    <span class="session-value"><?php echo htmlspecialchars($user_email); ?></span>
                </div>
                <div class="session-item">
                    <span class="session-key">Registration Date:</span>
                    <span class="session-value"><?php echo htmlspecialchars($registration_date); ?></span>
                </div>
                <div class="session-item">
                    <span class="session-key">Session ID:</span>
                    <span class="session-value"><?php echo session_id(); ?></span>
                </div>
                <div class="session-item">
                    <span class="session-key">Login Time:</span>
                    <span class="session-value"><?php echo date('Y-m-d H:i:s'); ?></span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>