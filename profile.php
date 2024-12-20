<?php
session_start();
include 'db_connect.php';
include 'session_manager.php';

// Check if user is logged in and authorized
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if (isset($_GET['user_id'])) {
    $userId = intval($_GET['user_id']);
    $query = "SELECT name, address, salary, dob, phone, email, gender, nationality, staffid, identificationNo, id_card, username FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        echo "<p>User not found.</p>";
        exit();
    }
} else {
    echo "<p>Invalid user ID.</p>";
    exit();
}

// File download handling
if (isset($_GET['download_id_card'])) {
    $idCardData = $user['id_card'];
    
    // Check if this is a data URL
    if (strpos($idCardData, 'data:') === 0) {
        if (preg_match('/^data:(application\/pdf|image\/(?:png|jpeg|jpg));base64,(.+)$/i', $idCardData, $matches)) {
            $mimeType = $matches[1];
            $base64Data = $matches[2];
        } else {
            die('Invalid data URL format');
        }
    } else {
        $base64Data = $idCardData;
        $decodedData = base64_decode($base64Data);
        
        $fileInfo = finfo_open();
        $mimeType = finfo_buffer($fileInfo, $decodedData, FILEINFO_MIME_TYPE);
        finfo_close($fileInfo);
        
        if (!in_array($mimeType, ['application/pdf', 'image/png', 'image/jpeg', 'image/jpg'])) {
            $mimeType = 'application/pdf';
        }
    }
    
    $fileData = base64_decode($base64Data);
    
    if ($fileData === false) {
        die('Error: Invalid Base64 data');
    }
    
    $extension = match($mimeType) {
        'application/pdf' => 'pdf',
        'image/png' => 'png',
        'image/jpeg', 'image/jpg' => 'jpg',
        default => 'pdf'
    };
    
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: attachment; filename="id_card.' . $extension . '"');
    header('Content-Length: ' . strlen($fileData));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: public');
    
    echo $fileData;
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - Admin View</title>
    <link rel="stylesheet" href="libs/css/bootstrap.min.css">
    <link rel="icon" href="img/favicon-32x32.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
    :root {
        --primary-color: #2563eb;
        --primary-hover: #1d4ed8;
        --bg-color: #f8fafc;
        --card-bg: #ffffff;
        --text-primary: #1e293b;
        --text-secondary: #64748b;
        --border-color: #e2e8f0;
    }

    body {
        /* background-color: var(--bg-color); */
        font-family: 'Inter', sans-serif;
        color: var(--text-primary);
        line-height: 1.6; /* Slightly increased line height for better readability */
        padding: 2rem; /* Increased padding for better spacing */

        background: url('img/background.jpeg') no-repeat center center fixed;
        background-size: cover;
    }

    .profile-container {
        background-color: var(--card-bg);
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        padding: 2.5rem;
        margin: 0 auto;
        max-width: 900px;
        width: 95%;
    }

    .profile-header {
        text-align: center;
        margin-bottom: 2.5rem; /* Increased margin for better spacing */
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--border-color);
        position: relative;
    }

    .profile-header h2 {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 2rem; /* Increased font size */
        margin: 0;
    }

    .profile-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.75rem; /* Increased gap between items */
    }

    .profile-item {
        padding: 1.5rem; /* Increased padding */
        background-color: var(--bg-color);
        border-radius: 8px;
        transition: transform 0.2s ease;
    }

    .profile-item:hover {
        transform: translateY(-2px);
    }

    .profile-item dt {
        font-weight: 600;
        font-size: 1rem; /* Slightly larger font size */
        color: var(--text-secondary);
        margin-bottom: 0.75rem; /* Increased margin for better spacing */
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }

    .profile-item dd {
        font-size: 1.125rem; /* Slightly larger font size */
        color: var(--text-primary);
        margin: 0;
        word-break: break-word;
    }

    .download-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 1rem 2rem; /* Larger padding */
        background-color: var(--primary-color);
        color: white;
        text-decoration: none;
        border-radius: 6px;
        font-weight: 500;
        font-size: 1rem; /* Larger font size */
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
    }

    .download-btn:hover {
        background-color: var(--primary-hover);
        color: white;
        text-decoration: none;
        transform: translateY(-1px);
    }

    .back-btn {
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        padding: 0.75rem 1.25rem; /* Larger padding */
        background-color: var(--text-secondary);
        color: white;
        text-decoration: none;
        border-radius: 6px;
        font-size: 1rem; /* Larger font size */
        transition: all 0.2s ease;
    }

    .back-btn:hover {
        background-color: var(--text-primary);
        color: white;
        text-decoration: none;
    }

    .profile-item.full-width {
        grid-column: 1 / -1;
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
        .profile-grid {
            grid-template-columns: 1fr;
            gap: 1.5rem; /* Increased gap for smaller screens */
        }
        
        .profile-container {
            padding: 2rem; /* Increased padding */
        }

        .profile-header {
            padding-top: 2.5rem;
        }

        .back-btn {
            top: 0;
            left: 50%;
            transform: translateX(-50%);
        }
    }
</style>

</head>
<body>
    <div class="container profile-container">
        <div class="profile-header">
            <a href="admin.php" class="back-btn">← Back to User Management</a>
            <h2>User Profile</h2>
        </div>
        
        <div class="profile-grid">
            <div class="profile-item">
                <dt>Name</dt>
                <dd><?php echo htmlspecialchars($user['name']); ?></dd>
            </div>

            <div class="profile-item">
                <dt>Staff ID</dt>
                <dd><?php echo htmlspecialchars($user['staffid']); ?></dd>
            </div>

            <div class="profile-item">
                <dt>Email</dt>
                <dd><?php echo htmlspecialchars($user['email']); ?></dd>
            </div>

            <div class="profile-item">
                <dt>Phone</dt>
                <dd><?php echo htmlspecialchars($user['phone']); ?></dd>
            </div>

            <div class="profile-item full-width">
                <dt>Address</dt>
                <dd><?php echo htmlspecialchars($user['address']); ?></dd>
            </div>

            <div class="profile-item">
                <dt>Date of Birth</dt>
                <dd><?php echo htmlspecialchars($user['dob']); ?></dd>
            </div>

            <div class="profile-item">
                <dt>Gender</dt>
                <dd><?php echo htmlspecialchars($user['gender']); ?></dd>
            </div>

            <div class="profile-item">
                <dt>Nationality</dt>
                <dd><?php echo htmlspecialchars($user['nationality']); ?></dd>
            </div>

            <div class="profile-item">
                <dt>Username</dt>
                <dd><?php echo htmlspecialchars($user['username']); ?></dd>
            </div>

            <div class="profile-item">
                <dt>Identification No</dt>
                <dd><?php echo htmlspecialchars($user['identificationNo']); ?></dd>
            </div>

            <div class="profile-item">
                <dt>Salary</dt>
                <dd><?php echo htmlspecialchars($user['salary']); ?></dd>
            </div>

            <div class="profile-item">
                <dt>ID Card</dt>
                <dd>
                    <a class="download-btn" href="?user_id=<?php echo $userId; ?>&download_id_card=1">
                        Download ID Card
                    </a>
                </dd>
            </div>
        </div>
    </div>
</body>
</html>