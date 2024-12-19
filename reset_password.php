<?php
session_start();
include 'db_connect.php';

if (isset($_GET['token'])) {
    $reset_token = $_GET['token'];

    // Query to check if the reset token is valid and not expired
    $query = "SELECT * FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $reset_token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $new_password = $_POST['new_password'];
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

            // Update the user's password
            $update_query = "UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("ss", $hashed_password, $reset_token);
            $update_stmt->execute();

            echo "Your password has been updated.";
        }
    } else {
        echo "Invalid or expired reset token.";
    }
} else {
    echo "No reset token provided.";
}
?>

<!DOCTYPE html>
<html lang="en-US">
<head>
    <title>Reset Password</title>
    <link rel="stylesheet" href="libs/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <h1>Reset Your Password</h1>
    <form method="post" action="reset_password.php?token=<?php echo $_GET['token']; ?>">
        <div class="form-group">
            <label>New Password</label>
            <input type="password" class="form-control" name="new_password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
    </form>
</div>
</body>
</html>
