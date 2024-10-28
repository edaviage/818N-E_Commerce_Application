<?php
include('../includes/connect.php');
include('../functions/common_functions.php');
include('../includes/session_handler.php');

// Include the AWS SDK for PHP
require_once __DIR__ . '/../vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ecommerce Admin Registration</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.css" />
    <link rel="stylesheet" href="../assets/css/main.css" />
</head>

<body>

    <!-- Start Landing Section -->
    <div class="landing admin-register">
        <div class="">
            <h2 class="text-center mb-1">Admin Registration</h2>
            <h4 class="text-center mb-3 fw-light">Create an account</h4>
            <div class="row m-0">
                <div class="col-md-6 p-0 d-none d-md-block">
                    <img src="../assets/images/bgregister.png" class="admin-register" alt="Register photo">
                </div>
                <div class="col-md-6 py-4 px-5 d-flex flex-column gap-4">
                    <div>
                        <form action="" method="post" class="d-flex flex-column gap-4" enctype="multipart/form-data">
                            <div class="form-outline">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" name="username" id="username" class="form-control" placeholder="Enter your username" required>
                            </div>
                            <div class="form-outline">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" id="email" class="form-control" placeholder="Enter your Email" required>
                            </div>
                            <div class="form-outline">
                                <label for="admin_image" class="form-label">Admin Image</label>
                                <input type="file" name="admin_image" id="admin_image" class="form-control" required>
                            </div>
                            <div class="form-outline">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" name="password" id="password" class="form-control" placeholder="Enter your Password" required>
                            </div>
                            <div class="form-outline">
                                <label for="conf_password" class="form-label">Confirm Password</label>
                                <input type="password" name="conf_password" id="conf_password" class="form-control" placeholder="Confirm your Password" required>
                            </div>
                            <div class="form-outline">
                                <input type="submit" value="Register" class="btn btn-primary mb-3" name="admin_register">
                                <p class="small">
                                    You already have an account? <a href="./admin_login.php" class="text-decoration-underline text-success fw-bold">Login</a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Landing Section -->





    <!-- Start Footer -->
    <!-- <div class="upper-nav primary-bg p-2 px-3 text-center text-break">
        <span>All CopyRight &copy;2023</span>
    </div> -->
    <!-- End Footer -->

    <script src="../assets/js/bootstrap.bundle.js"></script>
</body>

</html>
<!-- PHP Code -->
<?php
if (isset($_POST['admin_register'])) {
    // Get form data
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $conf_password = $_POST['conf_password'];

    // Validate password match
    if ($password != $conf_password) {
        echo "<script>window.alert('Passwords do not match');</script>";
        exit();
    }

    // Check if username or email already exists
    $select_query = "SELECT * FROM `admin_table` WHERE admin_name = ? OR admin_email = ?";
    $stmt = mysqli_prepare($con, $select_query);
    mysqli_stmt_bind_param($stmt, 'ss', $username, $email);
    mysqli_stmt_execute($stmt);
    $select_result = mysqli_stmt_get_result($stmt);
    $rows_count = mysqli_num_rows($select_result);

    if ($rows_count > 0) {
        echo "<script>window.alert('Username or Email already exist');</script>";
        exit();
    }

    // Hash the password
    $hash_password = password_hash($password, PASSWORD_DEFAULT);

    // Handle image upload
    if (isset($_FILES['admin_image']) && $_FILES['admin_image']['error'] == 0) {
        // Instantiate the S3 client
        $s3 = new S3Client([
            'version' => 'latest',
            'region'  => AWS_REGION,
        ]);

        $image_name = $_FILES['admin_image']['name'];
        $image_tmp = $_FILES['admin_image']['tmp_name'];
        $image_extension = pathinfo($image_name, PATHINFO_EXTENSION);

        // Generate a unique file name
        $filename = uniqid('IMG_', true) . '.' . $image_extension;

        try {
            // Upload data to S3
            $result = $s3->putObject([
                'Bucket' => S3_BUCKET,
                'Key'    => '/admin/admin_images/' . $filename,
                'SourceFile' => $image_tmp,
            ]);

            // Insert user into database
            $insert_query = "INSERT INTO `admin_table` (admin_name, admin_email, admin_image, admin_password) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($con, $insert_query);
            mysqli_stmt_bind_param($stmt, 'ssss', $username, $email, $filename, $hash_password);
            $insert_result = mysqli_stmt_execute($stmt);

            if ($insert_result) {
                echo "<script>window.alert('Admin Registration Successful!');</script>";
                $_SESSION['admin_username'] = $username;
                echo "<script>window.open('./index.php', '_self');</script>";
            } else {
                die(mysqli_error($con));
            }
        } catch (AwsException $e) {
            // Output error message if fails
            error_log("S3 Upload Error: " . $e->getMessage());
            echo "<script>window.alert('Failed to upload image. Please try again.');</script>";
        }
    } else {
        echo "<script>window.alert('Please select an image to upload');</script>";
    }
}
?>