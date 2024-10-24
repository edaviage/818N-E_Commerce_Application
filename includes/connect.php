<?php
        // Use the dynamically injected values for RDS connection
        $servername = getenv('RDS_HOST') ?: 'localhost';
        $username = getenv('RDS_USER') ?: 'root';
        $password = getenv('RDS_PASSWORD') ?: '';
        $dbname = getenv('RDS_DBNAME') ?: 'ecommerce_1';

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