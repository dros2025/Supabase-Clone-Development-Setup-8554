<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: login.html");
    exit();
}

$user_email = $_SESSION["email"];
$user_id = $_SESSION["user_id"];

// Check if user is admin (user_id = 1 or specific email)
$isAdmin = ($user_id == 1 || $user_email == 'admin@example.com');

if (!$isAdmin) {
    header("Location: dashboard.php");
    exit();
}

// Include database connection
require_once 'api/auth/db.php';

// Handle user deletion
if (isset($_POST['delete_user']) && isset($_POST['user_id'])) {
    $deleteUserId = $_POST['user_id'];
    
    // Don't allow admin to delete themselves
    if ($deleteUserId != $user_id) {
        try {
            $deleteUser = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $deleteUser->execute([$deleteUserId]);
            $deleteMessage = "User deleted successfully!";
        } catch (PDOException $e) {
            $deleteError = "Error deleting user: " . $e->getMessage();
        }
    } else {
        $deleteError = "You cannot delete your own account!";
    }
}

// Get all users
try {
    $getAllUsers = $pdo->query("SELECT id, email, created_at FROM users ORDER BY created_at DESC");
    $users = $getAllUsers->fetchAll();
    
    // Get user statistics
    $totalUsers = count($users);
    $todayUsers = 0;
    $weekUsers = 0;
    
    foreach ($users as $user) {
        $createdDate = new DateTime($user['created_at']);
        $today = new DateTime();
        $weekAgo = new DateTime('-7 days');
        
        if ($createdDate->format('Y-m-d') === $today->format('Y-m-d')) {
            $todayUsers++;
        }
        
        if ($createdDate >= $weekAgo) {
            $weekUsers++;
        }
    }
    
} catch (PDOException $e) {
    error_log("Admin panel error: " . $e->getMessage());
    $users = [];
    $totalUsers = 0;
    $todayUsers = 0;
    $weekUsers = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Supabase Clone</title>
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
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-email {
            color: #666;
            font-size: 14px;
        }
        
        .admin-badge {
            background: #10b981;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
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
        
        .admin-header {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            text-align: center;
        }
        
        .admin-title {
            font-size: 32px;
            color: #333;
            margin-bottom: 10px;
        }
        
        .admin-subtitle {
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
        
        .users-table {
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
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        
        .delete-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: background-color 0.3s;
        }
        
        .delete-btn:hover {
            background: #c82333;
        }
        
        .admin-badge-table {
            background: #10b981;
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .no-users {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .user-count {
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">Supabase Clone</div>
        <div class="nav-links">
            <a href="dashboard.php" class="nav-link">Dashboard</a>
            <a href="admin.php" class="nav-link admin-link">Admin Panel</a>
        </div>
        <div class="user-info">
            <span class="admin-badge">ADMIN</span>
            <span class="user-email"><?php echo htmlspecialchars($user_email); ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="admin-header">
            <h1 class="admin-title">Admin Panel</h1>
            <p class="admin-subtitle">Manage users and system settings</p>
        </div>

        <?php if (isset($deleteMessage)): ?>
            <div class="alert alert-success"><?php echo $deleteMessage; ?></div>
        <?php endif; ?>

        <?php if (isset($deleteError)): ?>
            <div class="alert alert-error"><?php echo $deleteError; ?></div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalUsers; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $todayUsers; ?></div>
                <div class="stat-label">New Today</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $weekUsers; ?></div>
                <div class="stat-label">New This Week</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo date('H:i'); ?></div>
                <div class="stat-label">Current Time</div>
            </div>
        </div>

        <div class="users-table">
            <div class="table-header">
                <span>All Users</span>
                <span class="user-count"><?php echo $totalUsers; ?> users registered</span>
            </div>
            <div class="table-content">
                <?php if (empty($users)): ?>
                    <div class="no-users">
                        <p>No users found in the database.</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Email</th>
                                <th>Registration Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($user['email']); ?>
                                        <?php if ($user['id'] == $user_id): ?>
                                            <span class="admin-badge-table">YOU</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M j, Y H:i', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <span class="status-badge status-active">Active</span>
                                    </td>
                                    <td>
                                        <?php if ($user['id'] != $user_id): ?>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" name="delete_user" class="delete-btn">Delete</button>
                                            </form>
                                        <?php else: ?>
                                            <span style="color: #666; font-size: 12px;">Cannot delete self</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Auto-refresh page every 30 seconds to show updated user data
        setTimeout(function() {
            window.location.reload();
        }, 30000);
    </script>
</body>
</html>