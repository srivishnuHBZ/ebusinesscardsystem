<?php
session_start();

include 'db_connect.php';

// Include PHPMailer files directly
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';  // Make sure PHPMailer is installed via Composer

// Redirect if the user is already logged in
if (isset($_SESSION['username'])) {
    if ($_SESSION['username'] === 'admin') {
        header("Location: admin.php");
    } else {
        header("Location: main.php");
    }
    exit();
}

// Handle Forgot Password Request
if (isset($_POST['forgot_password'])) {
    $email = $_POST['email'];

    // Check if email exists in the database
    $query = "SELECT * FROM qrcode.users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $reset_token = bin2hex(random_bytes(32));  // Generate a secure reset token
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour")); // Set expiration time to 1 hour from now

        // Update the reset token and its expiry in the database
        $update_query = "UPDATE qrcode.users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("sss", $reset_token, $expiry, $email);
        $update_stmt->execute();

        // Send reset link via email
        $reset_link = "https://ebusinesscardsystem.azurewebsites.net/reset_password.php?token=$reset_token"; // Change this URL accordingly

        $mail = new PHPMailer(true);  // Create a new PHPMailer instance

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true;
            $mail->Username   = 'jagaaathi030630@gmail.com';
            $mail->Password   = 'ufxt trvo prbh jprj'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('your_email@example.com', 'E-Business Card System');
            $mail->addAddress($email);            

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body    = "Hello, <br><br> We received a request to reset your password. Click the link below to reset your password:<br><br><a href='$reset_link'>$reset_link</a><br><br>This link will expire in 1 hour.";

            // Send email
            $mail->send();
            echo 'Password reset link has been sent to your email address.';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $error = "Email address not found!";
    }
}

// Handle login
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['forgot_password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query to fetch user data
    $query = "SELECT * FROM qrcode.users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists and password is correct
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['username'] = $username;

            // Redirect based on the username
            if ($username === 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: main.php");
            }
            exit();
        } else {
            $error = "Invalid username or password";
        }
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-BUSINESS CARD SYSTEM - Login</title>
    <link rel="stylesheet" href="libs/css/bootstrap.min.css">
    <style>
        body {
            background: url('img/background.jpeg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Arial', sans-serif;
            color: white;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            height: 100vh;
        }

        .login-container {
            background-color: rgba(0, 0, 0, 0.5);
            padding: 30px;
            border-radius: 15px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .logo {
            width: 150px;
            margin-bottom: 20px;
        }

        .login-container h2 {
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-control {
            border-radius: 10px;
            padding: 10px;
        }

        button {
            border-radius: 10px;
            padding: 10px;
            width: 100%;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #007bff;
        }

        .alert {
            margin-top: 10px;
            background-color: #f8d7da;
            color: #721c24;
            border-radius: 10px;
        }

        .text-center a {
            color: #ff6f61;
        }

        .text-center a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <img src="img/polilogo.png" class="logo" alt="Logo">
    <h1>E-BUSINESS CARD SYSTEM</h1>
    <div class="login-container">
        <h2>Login</h2>
        <?php if (isset($error)) { ?>
            <div class="alert"><?php echo $error; ?></div>
        <?php } ?>

        <!-- Login Form -->
        <?php if (!isset($_GET['token'])) { ?>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" class="form-control" name="username" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            <div class="text-center">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
                <p><a href="?forgot_password=true">Forgot Password?</a></p>
            </div>
        <?php } ?>

        <!-- Forgot Password Form -->
        <?php if (isset($_GET['forgot_password']) && $_GET['forgot_password'] == 'true') { ?>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" class="form-control" name="email" required>
                </div>
                <button type="submit" name="forgot_password" class="btn btn-danger">Send Reset Link</button>
            </form>
        <?php } ?>
    </div>
</div>
</body>
</html>
