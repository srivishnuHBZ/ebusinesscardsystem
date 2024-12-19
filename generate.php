<?php
include 'db_connect.php';

session_start();

// Check if the user is not logged in, redirect to the login page
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Logout logic
if(isset($_POST['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

if(isset($_POST['name']) && isset($_POST['position']) && isset($_POST['phone']) && isset($_POST['email']) && isset($_POST['address'])) {
    $name = $_POST['name'];
    $position = $_POST['position'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'];

    // Function to get username from email
    function getUsernameFromEmail($email) {
        $find = '@';
        $pos = strpos($email, $find);
        $username = substr($email, 0, $pos);
        return $username;
    }

    $username = getUsernameFromEmail($email);

    // QR code generation logic
    include('libs/phpqrcode/qrlib.php');

    $tempDir = 'temp/';
    $filename = $username;

    // QR code content
    $codeContents  = 'BEGIN:VCARD'."\n";
    $codeContents .= 'VERSION:2.1'."\n";
    $codeContents .= 'FN:'.$name."\n";
    $codeContents .= 'TEL;WORK;VOICE:'.$phone."\n";
    $codeContents .= 'ADR;TYPE=work;Address="HOME":'.$address.';'."\n";       
    $codeContents .= 'EMAIL:'.$email."\n";
    $codeContents .= 'END:VCARD';

    // Generate QR code
    QRcode::png($codeContents, $tempDir.$filename.'.png', QR_ECLEVEL_L, 3);
}
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <title>Generated QR Code</title>
    <link rel="icon" href="img/favicon-32x32.png" type="image/png">
    <link rel="stylesheet" href="libs/css/bootstrap.min.css">
    <link rel="stylesheet" href="libs/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="libs/navbarclock.js"></script>
    <style>
        .navbar-inverse {
            background-color: grey;
            border-color: #2c3e50;
        }
        .navbar-inverse .navbar-brand, .navbar-inverse a {
            color: #ecf0f1;
        }
        .navbar-inverse .navbar-brand:hover, .navbar-inverse a:hover {
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
        .qr-img {
            margin-top: 20px;
            text-align: center;
        }
        .card-list {
            list-style-type: none;
            padding: 0;
        }
        .card-list li {
            display: inline-block;
            margin-right: 10px;
        }
        .card-list a {
            display: block;
            padding: 10px;
            text-align: center;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }
        .blue {
            background-color: #3498db;
        }
        .purple {
            background-color: #9b59b6;
        }
        .red {
            background-color: #e74c3c;
        }
        .yellow {
            background-color: #f39c12;
        }
    </style>
</head>
<body onload="startTime()">
    <nav class="navbar navbar-inverse" role="navigation">
        <div class="container-fluid">
            <div class="navbar-header">
                <a class="navbar-brand" href="#">
                    <img src="img/polilogo.png" class="headerimg" alt="Logo">
                </a>
            </div>
            <form method="post" action="generate.php" id="logout-form" style="display: none;">
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
        <h3><strong>E-BUSINESS CARD SYSTEM</strong></h3>
        <div class="qr-img">
            <?php echo '<img src="temp/'.$filename.'.png" style="width:200px; height:200px;"><br>'; ?>
            <form action="create.php">
        <button type="submit" class="btn btn-primary">Edit Card</button>
    </form>
        </div>
        <div >
            <h2 style="text-align:center">SCAN ME !</h2>
            <center>
                <a class="btn btn-primary submitBtn" style="width:210px; margin:5px 0;" href="download.php?file=<?php echo $filename; ?>.png">Download QR Code</a>
                <h2 class="gb-head-text">Generate Business Card</h2>
                <ul class="card-list">
                    <li><a class="blue" href="vcard.php?color=blue&file=<?php echo $filename; ?>&name=<?php echo $name; ?>&phone=<?php echo $phone; ?>&email=<?php echo $email; ?>&address=<?php echo $address; ?>&position=<?php echo $position; ?>" target="_blank">Blue</a></li>
                    <li><a class="purple" href="vcard.php?color=purple&file=<?php echo $filename; ?>&name=<?php echo $name; ?>&phone=<?php echo $phone; ?>&email=<?php echo $email; ?>&address=<?php echo $address; ?>&position=<?php echo $position; ?>" target="_blank">Purple</a></li>
                    <li><a class="red" href="vcard.php?color=red&file=<?php echo $filename; ?>&name=<?php echo $name; ?>&phone=<?php echo $phone; ?>&email=<?php echo $email; ?>&address=<?php echo $address; ?>&position=<?php echo $position; ?>" target="_blank">Red</a></li>
                    <li><a class="yellow" href="vcard.php?color=yellow&file=<?php echo $filename; ?>&name=<?php echo $name; ?>&phone=<?php echo $phone; ?>&email=<?php echo $email; ?>&address=<?php echo $address; ?>&position=<?php echo $position; ?>" target="_blank">Yellow</a></li>
                </ul>
            </center>
        </div>
        <div class="clearfix"></div>
        <div class="dllink">
            <h4>Copyrights 2024, Universiti Tun Hussein Onn Malaysia (UTHM)</h4>
        </div>
    </div>
</body>
</html>
