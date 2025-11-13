<?php
session_start();
include('../includes/db_connect.php');

// Check if admin is logged in
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../admin/admin_login.php");
    exit();
}

$message = '';

// Handle form submission
if(isset($_POST['submit'])){
    $model = $conn->real_escape_string($_POST['model']);
    $launch_year = intval($_POST['launch_year']);
    $ram = intval($_POST['ram']);
    $storage = intval($_POST['storage']);
    $battery_capacity = intval($_POST['battery_capacity']);
    $launch_price = intval($_POST['launch_price']);

    // Insert into iphone_specs table
    $stmt = $conn->prepare("INSERT INTO iphone_specs (model, launch_year, ram, storage, battery_capacity, launch_price) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("siiiii", $model, $launch_year, $ram, $storage, $battery_capacity, $launch_price);

    if($stmt->execute()){
        $message = "iPhone spec added successfully!";
    } else {
        $message = "Error: " . $stmt->error;
    }
}
?>

<?php include('../includes/header.php'); ?>

<div class="container mt-5" style="max-width:600px;">
    <h2 class="mb-4 text-center">Add iPhone Specs (Admin Only)</h2>

    <?php if($message): ?>
        <div class="alert alert-success text-center"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">iPhone Model</label>
            <input type="text" name="model" class="form-control" placeholder="iPhone 5 / 6 / 7 ..." required>
        </div>

        <div class="mb-3">
            <label class="form-label">Launch Year</label>
            <input type="number" name="launch_year" class="form-control" placeholder="2012, 2013 ..." required>
        </div>

        <div class="mb-3">
            <label class="form-label">RAM (GB)</label>
            <input type="number" name="ram" class="form-control" placeholder="2, 3, 4 ..." required>
        </div>

        <div class="mb-3">
            <label class="form-label">Storage (GB)</label>
            <input type="number" name="storage" class="form-control" placeholder="16, 32, 64, 128 ..." required>
        </div>

        <div class="mb-3">
            <label class="form-label">Battery Capacity (mAh)</label>
            <input type="number" name="battery_capacity" class="form-control" placeholder="Example: 1810" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Launch Price (NPR)</label>
            <input type="number" name="launch_price" class="form-control" placeholder="Price in NPR" required>
        </div>

        <button type="submit" name="submit" class="btn btn-success w-100">Add iPhone</button>
    </form>
</div>

<?php include('../includes/footer.php'); ?>
