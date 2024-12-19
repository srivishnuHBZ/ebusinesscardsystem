 <?php
 try {
    $conn = new mysqli(
        '34.143.164.50',  // Ensure this is the correct Cloud SQL instance IP
        'ruthra',  // Create a specific user, not root
        'ruthra@123',  // Use a complex password
        'qrcode',  // Database name
        3306,  // Optional: specify port
        5  // Optional: connection timeout in seconds
    );

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    die("Database connection failed");
}