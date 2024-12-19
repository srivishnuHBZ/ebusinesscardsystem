<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_status'])) {
    $userId = intval($_POST['user_id']);
    $currentStatus = intval($_POST['current_status']);
    $newStatus = $currentStatus === 1 ? 0 : 1;

    $updateQuery = "UPDATE users SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ii", $newStatus, $userId);

    if ($stmt->execute()) {
        echo "<p style='color: green;'>User status updated successfully.</p>";
    } else {
        echo "<p style='color: red;'>Error updating user status: " . htmlspecialchars($stmt->error) . "</p>";
    }
}

$query = "SELECT id, username, name, email, status,staffid  FROM users WHERE username != 'admin'"; // Exclude admin account
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Page - User Management</title>
    <link rel="stylesheet" href="libs/css/bootstrap.min.css">
    <link rel="icon" href="img/favicon-32x32.png" type="image/png">
    <link rel="stylesheet" href="libs/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="libs/navbarclock.js"></script>
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        .admin-container {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 50px;
            max-width: 1000px;
            width: 95%;
        }
        .table-user-management {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
        }
        .table-user-management thead {
            background-color: #f1f5f9;
            color: #2c3e50;
        }
        .table-user-management thead th {
            font-weight: 600;
            border-bottom: 2px solid #e2e8f0;
            padding: 15px;
        }
        .table-user-management tbody tr {
            transition: background-color 0.3s ease;
        }
        .table-user-management tbody tr:hover {
            background-color: #f8fafc;
        }
        .table-user-management td {
            vertical-align: middle;
            padding: 15px;
            color: #2d3748;
        }
        .btn-toggle-status {
            background-color: #3182ce;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 8px 15px;
            font-weight: 500;
            transition: background-color 0.2s ease;
        }
        .btn-toggle-status:hover {
            background-color: #2c5282;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-active {
            background-color: #48bb78;
            color: white;
        }
        .status-inactive {
            background-color: #f56565;
            color: white;
        }

        .navbar-inverse {
            background-color: grey;
            border-color: #2c3e50;
            width: 100%;
        }
        .navbar-inverse .navbar-brand,
        .navbar-inverse a {
            color: #ecf0f1;
        }
        .navbar-inverse .navbar-brand:hover,
        .navbar-inverse a:hover {
            color: #ecdbff;
        }
        .headerimg {
            height: auto;
            width: 160px;
            margin: 5px;
            margin-top: -50px;
        }
        #clockdate {
            float: right;
            color: #ecf0f1;
        }
        .logout-icon {
            float: right;
            margin-top: 8px;
            color: #ecf0f1;
            font-size: 20px;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.9em;
            }
            .btn-toggle-status {
                padding: 6px 10px;
                font-size: 0.8em;
            }
        }
    </style>
</head>
<body onload="startTime()">
<nav class="navbar navbar-inverse" role="navigation">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" href="index.php">
                <img src="img/polilogo.png" class="headerimg" alt="Logo">
            </a>
        </div>
        <form method="post" action="main.php" id="logout-form" style="display: none;">
            <input type="hidden" name="logout">
        </form>
        <a class="logout-icon" onclick="document.getElementById('logout-form').submit();">
            <i class="fas fa-sign-out-alt"></i>
        </a>
        <div id="clockdate">
            <div class="clockdate-wrapper">
                <div id="clock"></div>
                <div id="date"><?php echo date('l, F j, Y'); ?></div>
            </div>
        </div>
    </div>
</nav>
    <div class="container admin-container">
        <h2 class="mb-4 text-center">User Management</h2>
        <div class="table-responsive">
            <table class="table table-user-management">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Staff ID</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['staffid']); ?></td>
                            <td>
                                <span class="status-badge <?php echo $row['status'] ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo $row['status'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <form method="post" action="admin.php">
                                    <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="current_status" value="<?php echo $row['status']; ?>">
                                    <button type="submit" name="toggle_status" class="btn btn-sm btn-toggle-status">
                                        Toggle Status
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>