<?php
session_start();
include('includes/db_connect.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$listing_id = $_GET['id'];

// Verify ownership
$check = $conn->prepare("SELECT * FROM listings WHERE id = ? AND user_id = ?");
$check->bind_param("ii", $listing_id, $user_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 1) {
    $conn->query("UPDATE listings SET status='Sold' WHERE id=$listing_id");
    $conn->query("UPDATE users SET trust_score = trust_score + 1 WHERE id=$user_id");
}

header("Location: dashboard.php");
exit();
?>
