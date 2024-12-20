<?php
session_start();
include 'db_connect.php';

if (isset($_SESSION['username'])) {
    header("Location: main.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Password strength requirements
    $password_pattern = "/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/";

    if ($password != $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (!preg_match($password_pattern, $password)) {
        $error = "Password must be at least 8 characters long, contain at least one letter, one number, and one special character.";
    } else {
        $query = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Username already taken.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (username, password) VALUES (?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ss", $username, $hashed_password);

            if ($stmt->execute()) {
                header("Location: index.php");
                exit();
            } else {
                $error = "Registration failed.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en-US">
<head>
    <title>E-BUSINESS CARD SYSTEM - Register</title>
    <link rel="stylesheet" href="libs/css/bootstrap.min.css">
    <style>
        body {
            background: url('img/background.jpeg') no-repeat center center fixed;
            background-size: cover;
            color: white;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            height: 100vh;
        }

        .register-container {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 400px;
        }

        .register-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo {
            width: 150px;
            margin-bottom: 20px;
        }

        .alert {
            margin-bottom: 15px;
        }

        .btn-block {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-block:hover {
            background-color: #0056b3;
            border-color: #004085;
        }

        .form-group label {
            font-weight: bold;
        }

        .form-group input {
            border-radius: 5px;
            padding: 10px;
        }

        #password-error {
            color: red;
            font-size: 14px;
        }

        .text-center p {
            margin-top: 10px;
        }

        h1 {
            margin-top: 20px;
            margin-bottom: 30px;
            text-align: center;
        }
    </style>
    <script>
        function validatePassword() {
            const password = document.getElementById("password").value;
            const confirmPassword = document.getElementById("confirm_password").value;
            const passwordPattern = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
            const errorElement = document.getElementById("password-error");

            if (!passwordPattern.test(password)) {
                errorElement.textContent = "Password must be at least 8 characters long, contain at least one letter, one number, and one special character.";
                return false;
            } else if (password !== confirmPassword) {
                errorElement.textContent = "Passwords do not match.";
                return false;
            }

            errorElement.textContent = "";
            return true;
        }
    </script>
</head>
<body>
<div class="container">
    <img src="img/polilogo.png" class="logo" alt="Logo">
    <h1>E-BUSINESS CARD SYSTEM</h1>
    <br/>
    <div class="register-container">
        <h2>Register</h2>
        <?php if (isset($error)) { ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php } ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" onsubmit="return validatePassword();">
            <div class="form-group">
                <label>Username</label>
                <input type="text" class="form-control" name="username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" id="password" class="form-control" name="password" required>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" id="confirm_password" class="form-control" name="confirm_password" required>
            </div>
            <span id="password-error"></span>
            <button type="submit" class="btn btn-primary btn-block">Register</button>
        </form>
        <div class="text-center">
            <p>Already have an account? <a href="index.php" style="color: #ffcc00;">Login here</a></p>
        </div>
    </div>
</div>
</body>
</html>
