<?php
include('../includes/session_handler.php');
unset($_SESSION['admin_username']);
session_destroy();
echo "<script>window.open('./admin_login.php','_self');</script>";
?>