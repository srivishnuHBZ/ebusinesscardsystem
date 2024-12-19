<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db_connect.php';

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];
$query = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '';
    $address = isset($_POST['address']) ? htmlspecialchars($_POST['address']) : '';
    $salary = floatval($_POST['salary']);
    $dob = $_POST['dob'];
    $phone = isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '';
    $email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '';
    $gender = $_POST['gender'];
    $nationality = isset($_POST['nationality']) ? htmlspecialchars($_POST['nationality']) : '';
    $identificationNo = isset($_POST['identificationNo']) ? htmlspecialchars($_POST['identificationNo']) : '';
    $staffId = isset($_POST['staffId']) ? htmlspecialchars($_POST['staffId']) : '';

    $id_card = null;
    if (isset($_FILES['id_card']) && $_FILES['id_card']['tmp_name']) {
        $id_card_tmp = $_FILES['id_card']['tmp_name'];
        $id_card_size = $_FILES['id_card']['size'];
        $id_card_type = $_FILES['id_card']['type'];

        if ($id_card_size > 5000000) {
            $error = "File size is too large. Please upload a file under 5MB.";
        } elseif (!in_array($id_card_type, ['image/jpeg', 'image/png', 'image/gif'])) {
            $error = "Invalid file type. Only JPG, PNG, and GIF files are allowed.";
        } else {
            $id_card = base64_encode(file_get_contents($id_card_tmp));
        }
    }

    if (isset($error)) {
        echo "<p style='color: red;'>$error</p>";
        exit();
    }

    $query = "UPDATE users 
              SET name = ?, address = ?, salary = ?, dob = ?, phone = ?, email = ?, gender = ?, nationality = ?, 
                  staffId = ?, identificationNo = ?, id_card = ? 
              WHERE username = ?";

    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        die("Error preparing statement: " . htmlspecialchars($conn->error));
    }

    $stmt->bind_param(
        "ssisssssssss",
        $name,
        $address,
        $salary,
        $dob,
        $phone,
        $email,
        $gender,
        $nationality,
        $staffId,
        $identificationNo,
        $id_card,
        $username
    );

    if ($stmt->execute()) {
        // Check if the user is active
        if ($user['status'] == 1) {
            header("Location: create.php");
            exit();
        } else {
            // If inactive, show the modal with the message
            echo "<script>
                    alert('Your account is not active yet. Please wait for admin approval.');
                    window.location.href = 'main.php'; // Redirect to main page after showing the message.
                  </script>";
        }
    } else {
        $error = "Error updating data: " . htmlspecialchars($stmt->error);
        
        error_log($error); 
    }

    if (isset($error)) {
        echo "<p style='color: red;'>$error</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en-US">
<head>
    <title>Profile Management - E-BUSINESS CARD SYSTEM</title>
    <link rel="icon" href="img/favicon-32x32.png" type="image/png">
    <link rel="stylesheet" href="libs/css/bootstrap.min.css">
    <link rel="stylesheet" href="libs/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="libs/navbarclock.js"></script>
    <style>
        body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f9f9f9;
            margin: 0;
        }
        .container {
            text-align: center;
            padding: 40px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .container h1 {
            margin-bottom: 20px;
        }
        .container a {
            text-decoration: none;
            color: #ffffff;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
        }
        .btn-primary:hover {
            background-color: #0056b3;
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
        .form-container {
            max-width: 600px;
            margin: 0 auto;
        }
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }
        .form-row {
            display: flex;
            flex-wrap: wrap;
        }
        .form-group.col-md-6 {
            width: 50%;
            padding: 0 10px;
        }
    </style>
</head>
<body class="body" onload="startTime()">
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
<div class="container">
    <h1>Profile Management</h1>
    <p>Please fill in your verification details before proceeding.</p>
    <div class="form-container">
        <form method="post" action="main.php" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Name</label>
                    <input type="text" class="form-control" name="name" placeholder="Enter your Name" required value="<?php echo $user['name']; ?>">
                </div>
                <div class="form-group col-md-6">
                    <label>Address</label>
                    <input type="text" class="form-control" name="address" placeholder="Enter your Address" required value="<?php echo $user['address']; ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Salary</label>
                    <input type="number" class="form-control" name="salary" placeholder="Enter your Salary" required value="<?php echo $user['salary']; ?>">
                </div>
                <div class="form-group col-md-6">
                    <label>Date of Birth</label>
                    <input type="date" class="form-control" name="dob" required value="<?php echo $user['dob']; ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Phone Number</label>
                    <input type="tel" class="form-control" name="phone" placeholder="Enter your Phone Number" required value="<?php echo $user['phone']; ?>">
                </div>
                <div class="form-group col-md-6">
                    <label>Email</label>
                    <input type="email" class="form-control" name="email" placeholder="Enter your Email" required value="<?php echo $user['email']; ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Gender</label>
                    <select class="form-control" name="gender">
                        <option value="male" <?php if ($user['gender'] == 'male') echo 'selected'; ?>>Male</option>
                        <option value="female" <?php if ($user['gender'] == 'female') echo 'selected'; ?>>Female</option>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label>Nationality</label>
                    <input type="text" class="form-control" name="nationality" placeholder="Enter your Nationality" value="<?php echo $user['nationality']; ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Identification No.</label>
                    <input type="text" class="form-control" name="identificationNo" placeholder="Enter your Identification No." value="<?php echo $user['identificationNo']; ?>">
                </div>
                <div class="form-group col-md-6">
                    <label>Staff ID</label>
                    <input type="text" class="form-control" name="staffId" placeholder="Enter your Staff ID" value="<?php echo $user['staffId']; ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="id_card">ID Card</label>
                    <input type="file" class="form-control" name="id_card" accept="image/*">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
    </div>
</div>

<!-- Modal for inactivity -->
<div class="modal" id="inactivityModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Inactive User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Your account is inactive. Please wait for admin approval.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="libs/js/bootstrap.bundle.min.js"></script>
<script src="libs/js/jquery.min.js"></script>
<script>
    <?php if ($user['status'] == 0): ?>
        $(document).ready(function() {
            $('#inactivityModal').modal('show');
        });
    <?php endif; ?>
</script>
</body>
</html>
