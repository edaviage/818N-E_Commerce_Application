<?php 

use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$host = $_ENV['DB_HOST'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];
$db_name = $_ENV['DB_NAME'];
$port = $_ENV['DB_PORT'];

$con = new mysqli($host, $username, $password, $db_name, $port);

if(!$con){
    die(mysqli_error($con));
}




?>