<?php
include("../includes/connect.php");
include_once("../includes/session_handler.php");
include("../functions/common_functions.php");

// Include the AWS SDK for PHP
require_once __DIR__ . '/../vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

// Define AWS configuration constants (if not defined already)
define('AWS_REGION', 'us-east-1'); // Replace with your AWS region
define('S3_BUCKET', 'your-s3-bucket-name'); // Replace with your S3 bucket name

if (!isset($_SESSION['username'])) {
    header('location:user_login.php');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- (rest of your HTML head section) -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $_SESSION['username']; ?> Profile</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.css" />
    <link rel="stylesheet" href="../assets/css/main.css" />
</head>

<body>
<!-- (rest of your HTML body content) -->

<!-- upper-nav -->
<div class="upper-nav primary-bg p-2 px-3 text-center text-break">
    <span>Summer Sale For All Swim Suits And Free Express Delivery - OFF 50%! <a>Shop Now</a></span>
</div>
<!-- upper-nav -->
<!-- Start NavBar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <!-- (rest of your navbar code) -->
</nav>
<!-- End NavBar -->

<!-- Start All Products -->
<div class="all-prod">
    <div class="container">
        <div class="sub-container pt-4 pb-4">
            <div class="categ-header">
                <div class="sub-title">
                    <span class="shape"></span>
                    <span class="title h3 text-dark">Profile</span>
                </div>
            </div>
            <div class="row mx-0">
                <div class="col-md-2 side-nav p-0">
                    <!-- side nav  -->
                    <!-- Profile Tabs -->
                    <ul class="navbar-nav me-auto navbar-profile">
                        <?php
                        $username = $_SESSION['username'];

                        // Fetch user data
                        $select_user_img = "SELECT * FROM `user_table` WHERE username=?";
                        $stmt = $con->prepare($select_user_img);
                        $stmt->bind_param('s', $username);
                        $stmt->execute();
                        $select_user_img_result = $stmt->get_result();
                        $row_user_img = $select_user_img_result->fetch_assoc();
                        $userImgKey = $row_user_img['user_image'];

                        // Generate the image URL directly from S3
                        $s3 = new S3Client([
                            'version' => 'latest',
                            'region'  => AWS_REGION,
                        ]);

                        $cmd = $s3->getCommand('GetObject', [
                            'Bucket' => S3_BUCKET,
                            'Key'    => $userImgKey,
                        ]);

                        $request = $s3->createPresignedRequest($cmd, '+20 minutes');

                        // Get the pre-signed URL
                        $userImgUrl = (string)$request->getUri();

                        echo "<li class='nav-item d-flex align-items-center gap-2'>
                                    <img src='$userImgUrl' alt='$username photo' class='img-profile img-thumbnail'/>
                                  </li>";
                        ?>
                        <!-- (rest of your navigation items) -->
                        <li class="nav-item d-flex align-items-center gap-2">
                            <a href="profile.php" class="nav-link fw-bold">
                                <h6>Pending Orders</h6>
                            </a>
                        </li>
                        <!-- (rest of your side navigation items) -->
                    </ul>
                </div>
                <div class="col-md-10">
                    <!-- Main View  -->
                    <div class="row">
                        <?php
                        get_user_order_details();
                        if (isset($_GET['edit_account'])) {
                            include('./edit_account.php');
                        }
                        if (isset($_GET['my_orders'])) {
                            include('./user_orders.php');
                        }
                        if (isset($_GET['delete_account'])) {
                            include('./delete_account.php');
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- (rest of your HTML body content) -->

<script src="../assets/js/bootstrap.bundle.js"></script>
</body>

</html>
