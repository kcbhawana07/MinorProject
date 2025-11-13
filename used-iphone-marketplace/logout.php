<?php
session_start();

// Remove user session
if(isset($_SESSION['user_id'])){
    unset($_SESSION['user_id']);
    unset($_SESSION['user_name']);
}

// Remove admin session
if(isset($_SESSION['admin_id'])){
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_username']);
}

// Remove role
if(isset($_SESSION['role'])){
    unset($_SESSION['role']);
}

// Destroy all session data
session_destroy();

// Redirect to homepage
header("Location: index.php");
exit();
?>
