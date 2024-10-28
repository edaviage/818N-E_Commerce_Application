<?php
include("../includes/connect.php");
include_once("../includes/session_handler.php");

// Include the AWS SDK for PHP
require_once __DIR__ . '/../vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

if (isset($_GET['edit_account'])) {
    $user_session_name = $_SESSION['username'];
    $select_user_query = "SELECT * FROM `user_table` WHERE username=?";
    $stmt = $con->prepare($select_user_query);
    $stmt->bind_param('s', $user_session_name);
    $stmt->execute();
    $select_user_result = $stmt->get_result();
    $row_user_fetch = $select_user_result->fetch_assoc();

    $user_id = $row_user_fetch['user_id'];
    $username = $row_user_fetch['username'];
    $user_email = $row_user_fetch['user_email'];
    $user_address = $row_user_fetch['user_address'];
    $user_mobile = $row_user_fetch['user_mobile'];
    $user_image_key = $row_user_fetch['user_image']; // S3 object key

    if ($user_image_key) {
        $user_image_url = '/users_area/user_images/' . urlencode($user_image_key);
    } else {
        $user_image_url = '/users_area/user_images/profile.png'; // Default image if none exists
    }
}

// Update data
if (isset($_POST['user_update'])) {
    $update_id = $user_id;
    $update_user = $_POST['user_username'];
    $update_email = $_POST['user_email'];
    $update_address = $_POST['user_address'];
    $update_mobile = $_POST['user_mobile'];

    if (isset($_FILES['user_image']) && $_FILES['user_image']['name'] != '') {
        // Handle image upload to S3
        $update_image_tmp = $_FILES['user_image']['tmp_name'];
        $update_image_name = $_FILES['user_image']['name'];
        $update_image_extension = pathinfo($update_image_name, PATHINFO_EXTENSION);

        // Generate a unique file name
        $filename = uniqid('IMG_', true) . '.' . $update_image_extension;

        try {
            // Upload data to S3
            $result = $s3->putObject([
                'Bucket' => S3_BUCKET,
                'Key'    => '/users_area/user_images/' . $filename,
                'SourceFile' => $update_image_tmp,
            ]);

            $update_image = $filename;

        } catch (AwsException $e) {
            // Output error message if fails
            error_log("S3 Upload Error: " . $e->getMessage());
            echo "<script>alert('Failed to upload image. Please try again.');</script>";
            $update_image = $user_image_key; // Keep old image
        }
    } else {
        $update_image = $user_image_key; // Keep old image
    }

    // Update query using prepared statements
    $update_query = "UPDATE `user_table` SET username=?, user_email=?, user_image=?, user_address=?, user_mobile=? WHERE user_id=?";
    $stmt = $con->prepare($update_query);
    $stmt->bind_param('sssssi', $update_user, $update_email, $update_image, $update_address, $update_mobile, $update_id);
    $update_result = $stmt->execute();

    if ($update_result) {
        $_SESSION['username'] = $update_user;
        echo "<script>window.alert('Data updated successfully');</script>";
        echo "<script>window.open('profile.php?edit_account','_self');</script>";
    } else {
        echo "<script>window.alert('Error updating data');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Account</title>
</head>

<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h3 class="text-center mb-3">Edit Account</h3>
            <form action="" method="post" enctype="multipart/form-data" class="d-flex flex-column gap-3">
                <div class="form-outline">
                    <label for="user_username" class="form-label">Username</label>
                    <input type="text" name="user_username" id="user_username" class="form-control" value="<?php echo htmlspecialchars($username); ?>">
                </div>
                <div class="form-outline">
                    <label for="user_email" class="form-label">Email</label>
                    <input type="email" name="user_email" id="user_email" class="form-control" value="<?php echo htmlspecialchars($user_email); ?>">
                </div>
                <div class="form-outline d-flex align-items-center gap-2">
                    <input type="file" name="user_image" id="user_image" class="form-control">
                    <?php if ($user_image_url): ?>
                        <img src="<?php echo $user_image_url; ?>" height="80px" alt="<?php echo htmlspecialchars($username); ?> Photo">
                    <?php else: ?>
                        <img src="./user_images/profile.png" height="80px" alt="Default Photo">
                    <?php endif; ?>
                </div>
                <div class="form-outline">
                    <label for="user_address" class="form-label">User Address</label>
                    <input type="text" name="user_address" id="user_address" class="form-control" value="<?php echo htmlspecialchars($user_address); ?>">
                </div>
                <div class="form-outline">
                    <label for="user_mobile" class="form-label">User Mobile</label>
                    <input type="text" name="user_mobile" id="user_mobile" class="form-control" value="<?php echo htmlspecialchars($user_mobile); ?>">
                </div>
                <div class="form-outline text-center">
                    <input type="submit" name="user_update" id="user_update" value="Update" class="btn btn-primary">
                </div>
            </form>
        </div>
    </div>
</div>
</body>

</html>
