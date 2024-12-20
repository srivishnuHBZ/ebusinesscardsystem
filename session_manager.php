<!-- <?php
session_start();

// Set session lifetime to 24 hours (86400 seconds)
$session_lifetime = 86400; // 24 hours in seconds

if (isset($_SESSION['last_activity'])) {
    $time_elapsed = time() - $_SESSION['last_activity'];
    if ($time_elapsed > $session_lifetime) {
        // Destroy session if inactivity exceeds 24 hours
        session_unset();
        session_destroy();
        header("Location: index.php"); // Redirect to login page
        exit();
    }
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();

// Optionally, verify if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: index.php"); // Redirect to login page
    exit();
}
?> -->
