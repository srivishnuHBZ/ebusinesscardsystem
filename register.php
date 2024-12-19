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

    if ($password != $confirm_password) {
        $error = "Passwords do not match";
    } else {
        $query = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Username already taken";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (username, password) VALUES (?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ss", $username, $hashed_password);

            if ($stmt->execute()) {
                // $_SESSION['username'] = $username;
                header("Location: index.php");
                exit();
            } else {
                $error = "Registration failed";
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
            background-color: black;
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
            width: 300px;
        }

        .register-container h2 {
            text-align: center;
        }

        .logo {
            width: 300px;
            height: 300px;
            margin-bottom: -100px;  
        }
    </style>
</head>
<body>
<div class="container">
    <img src="img/polilogo.png" class="logo" alt="Logo">
    <h1>E-BUSINESS CARD SYSTEM</h1>
    <br/>
    <div class="register-container">
        <h2>Register</h2>
        <?php if(isset($error)) { ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php } ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label>Username</label>
                <input type="text" class="form-control" name="username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" class="form-control" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Register</button>
        </form>
        <div class="text-center">
    <p>Already have an account? <a href="index.php">Login here</a></p>
</div>
    </div>
</>
</body>
</html>
