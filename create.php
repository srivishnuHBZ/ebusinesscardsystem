<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; 


include 'db_connect.php'; 


$username = $_SESSION['username'];
$sql = "SELECT staffid, phone, email FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $_SESSION['staffid'] = $row['staffid']; 
    $_SESSION['phone'] = $row['phone']; 
    $_SESSION['user_email'] = $row['email'];
} else {
    echo "Staff details not found for the current user.";
    exit();
}


if (isset($_POST['action']) && $_POST['action'] == 'send_otp') {
 
    
    $staffid = $_SESSION['staffid'];
    $staffidSuffix = substr($staffid, -2);

    $randomNumber = mt_rand(1000, 9999);

    $otp = $staffidSuffix . '-' . $randomNumber;
    

    $_SESSION['generatedOtp'] = $otp;
    $_SESSION['otpTimestamp'] = time();

    
    $mail = new PHPMailer(true);

    try {
        
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; 
        $mail->SMTPAuth   = true;
        $mail->Username   = 'jagaaathi030630@gmail.com'; // SMTP username
        $mail->Password   = 'ufxt trvo prbh jprj'; // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        
        $mail->setFrom('noreply@yourcompany.com', 'E-Business Card System');
        $mail->addAddress($_SESSION['user_email']);

        
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP for E-Business Card Generation';
        $mail->Body    = "Your One-Time Password (OTP) is: <b>$otp</b><br>This OTP is valid for 10 minutes.";

        $mail->send();
        echo json_encode(['status' => 'success', 'message' => 'OTP sent successfully']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => "OTP could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
    }
    exit();
}


if (isset($_POST['action']) && $_POST['action'] == 'verify_otp') {
    $userOtp = $_POST['otp'];
    $currentTime = time();
    
    // Debugging: Output user OTP and generated OTP
    error_log("User OTP: " . $userOtp); // Log the entered OTP
    error_log("Generated OTP: " . $_SESSION['generatedOtp']); // Log the stored OTP

    // Check OTP format and expiration
    if (isset($_SESSION['generatedOtp']) && 
        $userOtp === $_SESSION['generatedOtp'] && 
        ($currentTime - $_SESSION['otpTimestamp']) <= 600) {
        
        echo json_encode(['status' => 'success', 'message' => 'OTP verified']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid or expired OTP']);
    }
    exit();
}

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en-US">
<head>
    <title>Create E-BUSINESS CARD</title>
    <link rel="icon" href="img/favicon-32x32.png" type="image/png">
    <link rel="stylesheet" href="libs/css/bootstrap.min.css">
    <link rel="stylesheet" href="libs/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="libs/navbarclock.js"></script>
    <style>
        .navbar-inverse {
            background-color: grey;
            border-color: #2c3e50;
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
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .submitBtn {
            width: 100%;
        }
        .dllink {
            text-align: center;
            margin-top: 20px;
        }
        #otpMessage {
            margin-top: 10px;
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
            <form method="post" action="create.php" id="logout-form" style="display: none;">
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
    <div class="myoutput">
        <h3><strong>Create Your E-BUSINESS CARD</strong></h3>
        <div class="form-container">
            <h3>Contact Details</h3>
            <form method="post" action="generate.php" id="businessCardForm">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" class="form-control" name="name" placeholder="Enter your Name" required>
                </div>
                <div class="form-group">
                    <label>Position</label>
                    <input type="text" class="form-control" name="position" placeholder="Enter your Position" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="number" class="form-control" name="phone" value="<?php echo $_SESSION['phone']; ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" class="form-control" name="email" value="<?php echo $_SESSION['user_email']; ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <input type="text" class="form-control" name="address" placeholder="Enter your Address" required>
                </div>
                <div class="form-group">
                    <label>OTP</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="otp" name="otp" placeholder="Enter OTP" required>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-secondary" id="sendOtpButton">Send OTP</button>
                        </div>
                    </div>
                    <div id="otpMessage" class="text-center"></div>
                </div>
                <div class="form-group">
                    <input type="submit" class="btn btn-primary submitBtn" id="generateButton" value="Generate QR Code" disabled>
                </div>
            </form>
        </div>
        <div class="clearfix"></div>
        <div class="dllink">
            <h4>Copyrights 2024, Universiti Tun Hussein Onn Malaysia (UTHM)</h4>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        
        $('#sendOtpButton').click(function() {
            $.ajax({
                url: '',
                method: 'POST',
                data: { action: 'send_otp' },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#otpMessage').html('<div class="alert alert-success">OTP sent to your email</div>');
                        $('#sendOtpButton').prop('disabled', true);
                        setTimeout(function() {
                            $('#sendOtpButton').prop('disabled', false);
                        }, 60000); 
                    } else {
                        $('#otpMessage').html('<div class="alert alert-danger">' + response.message + '</div>');
                    }
                },
                error: function() {
                    $('#otpMessage').html('<div class="alert alert-danger">Failed to send OTP. Please try again.</div>');
                }
            });
        });

       
        $('#otp').on('input', function() {
            var otpValue = $(this).val().trim();
            
            if (otpValue.length === 7) {
                $.ajax({
                    url: '',
                    method: 'POST',
                    data: { 
                        action: 'verify_otp', 
                        otp: otpValue 
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#otpMessage').html('<div class="alert alert-success">OTP verified successfully</div>');
                            $('#generateButton').prop('disabled', false);
                        } else {
                            $('#otpMessage').html('<div class="alert alert-danger">' + response.message + '</div>');
                            $('#generateButton').prop('disabled', true);
                        }
                    },
                    error: function() {
                        $('#otpMessage').html('<div class="alert alert-danger">Verification failed. Please try again.</div>');
                        $('#generateButton').prop('disabled', true);
                    }
                });
            }
        });
    });
    </script>
</body>
</html>