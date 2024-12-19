<?php
session_start();
include 'db_connect.php';
use PHPMailer\PHPMailer\PHPMailer;


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Query to check if the email exists in the database
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $reset_token = bin2hex(random_bytes(32)); // Create a secure token

        // Save the reset token and its expiration time
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));
        $update_query = "UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("sss", $reset_token, $expiry, $email);
        $update_stmt->execute();

        // Send reset email using PHPMailer
        $mail = new PHPMailer;
        $mail->setFrom('your-email@example.com', 'E-Business Card System');
        $mail->addAddress($email);
        $mail->Subject = 'Password Reset Request';
        $mail->Body    = 'Click the link to reset your password: ' .
                         'http://yourwebsite.com/reset_password.php?token=' . $reset_token;

        if ($mail->send()) {
            echo "An email has been sent with instructions to reset your password.";
        } else {
            echo "Error sending email: " . $mail->ErrorInfo;
        }
    } else {
        echo "No account found with that email address.";
    }
}
?>

<!DOCTYPE html>
<html lang="en-US">
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="libs/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <h1>Forgot Password</h1>
    <form method="post" action="forgot_password.php">
        <div class="form-group">
            <label>Email</label>
            <input type="email" class="form-control" name="email" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
    </form>
</div>
</body>
</html>
