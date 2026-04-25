<?php
session_start();

require_once("../DBController.php");
require_once("../classes/User.php");
require_once("../classes/Admin.php");

$db = new DBController();
$db->openConnection();

$admin = new Admin($db->connection);
$user = new User($db->connection);

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../FrontEnd/register.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ban_user'])) {
        $admin->banUser($_POST['user_id']);
    } elseif (isset($_POST['unban_user'])) {
        $admin->unbanUser($_POST['user_id']);
    } elseif (isset($_POST['delete_post'])) {
        $admin->deletePost($_POST['post_id']);
    }
    header("Location: admindashboard.php");
    exit;
}

$allUsers = $admin->getAllUsers();
$allPosts = $admin->getAllPosts();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ElevateU Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&family=Poppins:wght@500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 50%, #cbd5e1 100%);
            min-height: 100vh;
            color: #1a202c;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><g fill="%2300d4aa" fill-opacity="0.05"><circle cx="30" cy="30" r="1.5"/></g></svg>') repeat;
            pointer-events: none;
            z-index: -1;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .header {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid rgba(0, 212, 170, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #00d4aa, #00b4d8, #0077b6);
            background-size: 200% 100%;
            animation: shimmer 3s ease-in-out infinite;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .header-info h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 8px;
            background: linear-gradient(135deg, #00d4aa, #2d3748);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .header-info p {
            font-size: 1.1rem;
            color: #64748b;
            font-weight: 400;
        }

        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .admin-badge {
            background: linear-gradient(135deg, #00d4aa, #00b4d8);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(0, 212, 170, 0.3);
        }

        .logout-btn {
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 107, 107, 0.4);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 25px;
            border: 1px solid rgba(0, 212, 170, 0.2);
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 212, 170, 0.2);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color:rgb(1, 156, 125);
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 1rem;
            color: #64748b;
            font-weight: 500;
        }

        .tabs {
            display: flex;
            margin-bottom: 30px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 8px;
            border: 1px solid rgba(0, 212, 170, 0.2);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .tab-btn {
            flex: 1;
            padding: 15px 25px;
            background: transparent;
            border: none;
            color: #64748b;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            border-radius: 12px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .tab-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 212, 170, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .tab-btn:hover::before {
            left: 100%;
        }

        .tab-btn.active {
            background: linear-gradient(135deg, #00d4aa, #00b4d8);
            color: white;
            box-shadow: 0 4px 15px rgba(0, 212, 170, 0.3);
        }

        .tab-content {
            display: none;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 30px;
            border: 1px solid rgba(0, 212, 170, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .table-container {
            overflow-x: auto;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.5);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: transparent;
        }

        th {
            background: rgba(0, 212, 170, 0.1);
            color: #2d3748;
            padding: 18px 20px;
            text-align: left;
            font-weight: 600;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid rgba(0, 212, 170, 0.2);
        }

        td {
            padding: 18px 20px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            color: #4a5568;
            font-weight: 400;
            vertical-align: middle;
        }

        tr:hover {
            background: rgba(0, 212, 170, 0.03);
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 107, 107, 0.4);
        }

        .btn-warning {
            background: linear-gradient(135deg, #ffd93d, #ff9800);
            color: #333;
            box-shadow: 0 4px 15px rgba(255, 217, 61, 0.3);
        }

        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 217, 61, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #00d4aa, #00b4d8);
            color: white;
            box-shadow: 0 4px 15px rgba(0, 212, 170, 0.3);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 212, 170, 0.4);
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-active {
            background: linear-gradient(135deg, #00d4aa, #00b4d8);
            color: white;
            box-shadow: 0 2px 8px rgba(0, 212, 170, 0.3);
        }

        .badge-banned {
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
            color: white;
            box-shadow: 0 2px 8px rgba(255, 107, 107, 0.3);
        }

        .content-preview {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        @keyframes shimmer {
            0%, 100% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px 15px;
            }
            
            .header-content {
                flex-direction: column;
                text-align: center;
            }
            
            .header-info h1 {
                font-size: 2rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .tabs {
                flex-direction: column;
            }
            
            .tab-btn {
                margin-bottom: 5px;
            }
            
            .table-container {
                font-size: 0.9rem;
            }
            
            th, td {
                padding: 12px 15px;
            }
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(0, 212, 170, 0.3);
            border-radius: 50%;
            border-top-color: #00d4aa;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="header-content">
            <div class="header-info">
                <h1>ElevateU Admin</h1>
            </div>
            <div class="header-actions">
                <div class="admin-badge">Admin Panel</div>
                <a href="../classes/logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?= count($allUsers) ?></div>
            <div class="stat-label">Total Users</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= count($allPosts) ?></div>
            <div class="stat-label">Total Posts</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= count(array_filter($allUsers, fn($u) => $u['isBanned'])) ?></div>
            <div class="stat-label">Banned Users</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= count(array_filter($allUsers, fn($u) => !$u['isBanned'])) ?></div>
            <div class="stat-label">Active Users</div>
        </div>
    </div>

    <div class="tabs">
        <button class="tab-btn active" data-tab="users">User Management</button>
        <button class="tab-btn" data-tab="posts">Post Management</button>
    </div>

    <div id="users-tab" class="tab-content active">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allUsers as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['userID']) ?></td>
                            <td><?= htmlspecialchars($u['name']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <span class="badge <?= $u['isBanned'] ? 'badge-banned' : 'badge-active' ?>">
                                    <?= $u['isBanned'] ? 'Banned' : 'Active' ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?= $u['userID'] ?>">
                                    <?php if ($u['isBanned']): ?>
                                        <button type="submit" name="unban_user" class="btn btn-success">Unban</button>
                                    <?php else: ?>
                                        <button type="submit" name="ban_user" class="btn btn-warning">Ban</button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="posts-tab" class="tab-content">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Content Preview</th>
                        <th>Author</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allPosts as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['post_id']) ?></td>
                            <td>
                                <div class="content-preview" title="<?= htmlspecialchars($p['post_description']) ?>">
                                    <?= htmlspecialchars($p['post_description']) ?>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($p['name']) ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="post_id" value="<?= $p['post_id'] ?>">
                                    <button type="submit" name="delete_post" class="btn btn-danger" 
                                            onclick="return confirm('Are you sure you want to delete this post?')">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            tabButtons.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById(`${btn.dataset.tab}-tab`).classList.add('active');
        });
    });

    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            console.log('Form submitted:', this);
        });
    });
</script>
</body>
</html>