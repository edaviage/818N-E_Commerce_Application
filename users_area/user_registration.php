<?php
require __DIR__ . '/../vendor/autoload.php';
use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Dotenv\Dotenv;

include('../includes/connect.php');
include('../functions/common_functions.php');
include('../config.php');

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();



?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ecommerce User Registeration Page</title>
    <link rel="stylesheet" href="<?php echo $static_base_url; ?>/assets/css/bootstrap.css" />
    <link rel="stylesheet" href="<?php echo $static_base_url; ?>/assets/css/main.css" />
</head>

<body>

    <div class="register">
        <div class="container py-3">
            <h2 class="text-center mb-4">New User Registration</h2>
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <form action="" method="post" enctype="multipart/form-data" class="d-flex flex-column gap-4">
                        <!-- username field  -->
                        <div class="form-outline">
                            <label for="user_username" class="form-label">Username</label>
                            <input type="text" placeholder="Enter your username" autocomplete="off" required="required" name="user_username" id="user_username" class="form-control">
                        </div>
                        <!-- email field  -->
                        <div class="form-outline">
                            <label for="user_email" class="form-label">Email</label>
                            <input type="email" placeholder="Enter your email" autocomplete="off" required="required" name="user_email" id="user_email" class="form-control">
                        </div>
                        <!-- image field  -->
                        <div class="form-outline">
                            <label for="user_image" class="form-label">User Image</label>
                            <input type="file" required="required" name="user_image" id="user_image" class="form-control">
                        </div>
                        <!-- password field  -->
                        <div class="form-outline">
                            <label for="user_password" class="form-label">Password</label>
                            <input type="password" placeholder="Enter your password" autocomplete="off" required="required" name="user_password" id="user_password" class="form-control">
                        </div>
                        <!-- confirm password field  -->
                        <div class="form-outline">
                            <label for="conf_user_password" class="form-label">Confirm Password</label>
                            <input type="password" placeholder="Confirm your password" autocomplete="off" required="required" name="conf_user_password" id="conf_user_password" class="form-control">
                        </div>
                        <!-- address field  -->
                        <div class="form-outline">
                            <label for="user_address" class="form-label">Address</label>
                            <input type="text" placeholder="Enter your address" autocomplete="off" required="required" name="user_address" id="user_address" class="form-control">
                        </div>
                        <!-- mobile field  -->
                        <div class="form-outline">
                            <label for="user_mobile" class="form-label">Mobile</label>
                            <input type="text" placeholder="Enter your mobile" autocomplete="off" required="required" name="user_mobile" id="user_mobile" class="form-control">
                        </div>
                        <div>
                            <input type="submit" value="Register" class="btn btn-primary mb-2" name="user_register">
                            <p>
                                Already have an account? <a href="user_login.php" class="text-primary text-decoration-underline"><strong>Login</strong></a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="<?php echo $static_base_url; ?>/assets//js/bootstrap.bundle.js"></script>
</body>

</html>
<!-- php code  -->
<?php
if (isset($_POST['user_register'])) {
    $user_username = $_POST['user_username'];
    $user_email = $_POST['user_email'];
    $user_password = $_POST['user_password'];
    $conf_user_password = $_POST['conf_user_password'];
    $hash_password = password_hash($user_password, PASSWORD_DEFAULT);
    $user_address = $_POST['user_address'];
    $user_mobile = $_POST['user_mobile'];
    $user_image = $_FILES['user_image']['name'];
    $user_image_tmp = $_FILES['user_image']['tmp_name'];
    $user_ip = getIPAddress();

    // Check if user exists
    $select_query = "SELECT * FROM `user_table` WHERE username='$user_username' OR user_email='$user_email'";
    $select_result = mysqli_query($con, $select_query);
    $rows_count = mysqli_num_rows($select_result);
    if ($rows_count > 0) {
        echo "<script>window.alert('Username | Email already exist');</script>";
    } else if ($user_password != $conf_user_password) {
        echo "<script>window.alert('Passwords do not match');</script>";
    } else {
        // Upload image to S3
        $s3Client = new S3Client([
            'region'  => $_ENV['AWS_REGION'],
            // 'version' => 'latest',
            'credentials' => [
                'key'    => $_ENV['AWS_ACCESS_KEY_ID'],
                'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'],
            ],
        ]);

        $bucket = $_ENV['S3_BUCKET_NAME'];
        $key = 'user_images/' . $user_username . '/' . basename($user_image);

        try {
            $result = $s3Client->putObject([
                'Bucket' => $bucket,
                'Key'    => $key,
                'SourceFile' => $user_image_tmp,
                'ACL'    => 'public-read', // Optional: make the file publicly accessible
            ]);
            $imageUrl = $result['ObjectURL'];

            // Insert user data into the database
            $insert_query = "INSERT INTO `user_table` (username, user_email, user_password, user_image, user_ip, user_address, user_mobile) VALUES ('$user_username', '$user_email', '$hash_password', '$imageUrl', '$user_ip', '$user_address', '$user_mobile')";
            $insert_result = mysqli_query($con, $insert_query);
            if ($insert_result) {
                echo "<script>window.alert('User registered successfully');</script>";
                echo "<script>window.open('user_login.php', '_self');</script>";
            } else {
                die(mysqli_error($con));
            }
        } catch (AwsException $e) {
            echo "Error uploading image: " . $e->getMessage();
        }
        // move_uploaded_file($user_image_tmp, "./user_images/$user_image");
        // $insert_query = "INSERT INTO `user_table` (username,user_email,user_password,user_image,user_ip,user_address,user_mobile) VALUES ('$user_username','$user_email','$hash_password','$user_image','$user_ip','$user_address','$user_mobile')";
        // $insert_result = mysqli_query($con, $insert_query);
        // if ($insert_result) {
        //     echo "<script>window.alert('User added successfully');</script>";
        // } else {
        //     die(mysqli_error($con));
        // }
    }
    // //select cart items check if items in cart go to checkout !| go to index.php
    // $select_cart_items = "SELECT * FROM `card_details` WHERE ip_address='$user_ip'";
    // $select_cart_items_result = mysqli_query($con,$select_cart_items);
    // $rows_count_cart_items = mysqli_num_rows($select_cart_items_result);
    // if($rows_count_cart_items > 0 ){
    //     $_SESSION['username'] = $user_username;
    //     echo "<script>window.alert('You have items in your cart');</script>";
    //     echo "<script>window.open('checkout.php','_self');</script>";
    // }else{
    //     echo "<script>window.open('../index.php','_self');</script>";
    // }
}
?>