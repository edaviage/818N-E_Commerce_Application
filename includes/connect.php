<?php
        require __DIR__ . '/../vendor/autoload.php';
        use Dotenv\Dotenv;
        $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->load();

        // Use the dynamically injected values for RDS connection
        $servername = $_ENV['RDS_HOST'] ?: 'localhost';
        $username = $_ENV['RDS_USER']?: 'root';
        $password = $_ENV['RDS_PASSWORD'] ?: '';
        $dbname = $_ENV['RDS_DBNAME'] ?: 'ecommerce_1';

        // Create connection

	$con = mysqli_init();
	mysqli_ssl_set($con, NULL, NULL, '/var/www/html/includes/db-ssl-cert.pem', NULL, NULL);
	mysqli_real_connect($con, $servername, $username, $password, $dbname, 3306, NULL, MYSQLI_CLIENT_SSL);

        //$con = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($con->connect_error) {
            die("Connection failed: " . $con->connect_error);
        }
?>