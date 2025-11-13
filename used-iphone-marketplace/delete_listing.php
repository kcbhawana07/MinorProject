<?php
session_start();
include('includes/db_connect.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$listing_id = $_GET['id'];

$stmt = $conn->prepare("DELETE FROM listings WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $listing_id, $user_id);
$stmt->execute();

header("Location: dashboard.php");
exit();
?>
