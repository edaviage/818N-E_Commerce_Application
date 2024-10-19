<?php 
// Database connection parameters
$servername = 'your_rds_endpoint'; // Replace with your RDS endpoint
$username = 'admin'; // Your RDS master username
$password = 'MyPassword123!'; // Your RDS master password
$dbname = 'ecommerce_1'; // Your database name

// Create connection
$con = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}
echo "Connected successfully";
?>
