<?php
include('../includes/connect.php');
include('../includes/session_handler.php');
include('../functions/common_functions.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ecommerce User Registration Page</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.css" />
    <link rel="stylesheet" href="../assets/css/main.css" />
</head>
<body>
    <div class="register">
        <div class="container py-3">
            <h2 class="text-center mb-4">New User Registration</h2>
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <form action="" method="post" enctype="multipart/form-data" class="d-flex flex-column gap-4">
                        <!-- Username Field -->
                        <div class="form-outline">
                            <label for="user_username" class="form-label">Username</label>
                            <input type="text" placeholder="Enter your username" autocomplete="off" required name="user_username" id="user_username" class="form-control">
                        </div>
                        <!-- Email Field -->
                        <div class="form-outline">
                            <label for="user_email" class="form-label">Email</label>
                            <input type="email" placeholder="Enter your email" autocomplete="off" required name="user_email" id="user_email" class="form-control">
                        </div>
                        <!-- Image Field -->
                        <div class="form-outline">
                            <label for="user_image" class="form-label">User Image</label>
                            <input type="file" required name="user_image" id="user_image" class="form-control">
                        </div>
                        <!-- Password Field -->
                        <div class="form-outline">
                            <label for="user_password" class="form-label">Password</label>
                            <input type="password" placeholder="Enter your password" autocomplete="off" required name="user_password" id="user_password" class="form-control">
                        </div>
                        <!-- Confirm Password Field -->
                        <div class="form-outline">
                            <label for="conf_user_password" class="form-label">Confirm Password</label>
                            <input type="password" placeholder="Confirm your password" autocomplete="off" required name="conf_user_password" id="conf_user_password" class="form-control">
                        </div>
                        <!-- Address Field -->
                        <div class="form-outline">
                            <label for="user_address" class="form-label">Address</label>
                            <input type="text" placeholder="Enter your address" autocomplete="off" required name="user_address" id="user_address" class="form-control">
                        </div>
                        <!-- Mobile Field -->
                        <div class="form-outline">
                            <label for="user_mobile" class="form-label">Mobile</label>
                            <input type="text" placeholder="Enter your mobile" autocomplete="off" required name="user_mobile" id="user_mobile" class="form-control">
                        </div>
                        <!-- Submit Button -->
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
    <script src="../assets/js/bootstrap.bundle.js"></script>
</body>
</html>
<!-- PHP Code -->
<?php
if (isset($_POST['user_register'])) {
    include('../includes/connect.php');

    $user_username = $_POST['user_username'];
    $user_email = $_POST['user_email'];
    $user_password = $_POST['user_password'];
    $conf_user_password = $_POST['conf_user_password'];
    $user_address = $_POST['user_address'];
    $user_mobile = $_POST['user_mobile'];
    $user_image = $_FILES['user_image']['name'];
    $user_image_tmp = $_FILES['user_image']['tmp_name'];
    $user_ip = getIPAddress();

    // Validate password match
    if ($user_password != $conf_user_password) {
        echo "<script>alert('Passwords do not match');</script>";
        exit();
    }

    // Check if username or email already exists
    $select_query = "SELECT * FROM `user_table` WHERE username=? OR user_email=?";
    $stmt = mysqli_prepare($con, $select_query);
    mysqli_stmt_bind_param($stmt, 'ss', $user_username, $user_email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows_count = mysqli_num_rows($result);

    if ($rows_count > 0) {
        echo "<script>alert('Username or Email already exists');</script>";
        exit();
    }

    // Hash the password
    $hash_password = password_hash($user_password, PASSWORD_DEFAULT);

    // Handle image upload
    $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
    $user_image_extension = pathinfo($user_image, PATHINFO_EXTENSION);

    if (!in_array(strtolower($user_image_extension), $allowed_extensions)) {
        echo "<script>alert('Invalid image file type');</script>";
        exit();
    }

    // Generate a unique file name
    $user_image_new_name = uniqid('IMG_', true) . '.' . $user_image_extension;

    if (move_uploaded_file($user_image_tmp, "./user_images/$user_image_new_name")) {
        // File uploaded successfully
    } else {
        echo "<script>alert('Failed to upload image');</script>";
        exit();
    }

    // Insert user into database
    $insert_query = "INSERT INTO `user_table` (username, user_email, user_password, user_image, user_ip, user_address, user_mobile) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $insert_query);
    mysqli_stmt_bind_param($stmt, 'sssssss', $user_username, $user_email, $hash_password, $user_image_new_name, $user_ip, $user_address, $user_mobile);
    $insert_result = mysqli_stmt_execute($stmt);

    if ($insert_result) {
        echo "<script>alert('Registration successful');</script>";
        $_SESSION['username'] = $user_username;
        echo "<script>window.open('../index.php', '_self');</script>";
    } else {
        die(mysqli_error($con));
    }
}
?>
