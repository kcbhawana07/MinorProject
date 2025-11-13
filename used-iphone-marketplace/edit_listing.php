<?php
session_start();
include('includes/db_connect.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if listing ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$listing_id = intval($_GET['id']);

// Fetch listing
$stmt = $conn->prepare("SELECT * FROM listings WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $listing_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "Listing not found or you do not have permission!";
    exit();
}

$listing = $result->fetch_assoc();
$error = $success = '';

if (isset($_POST['update'])) {
    $model = trim($_POST['model']);
    $launch_year = intval($_POST['launch_year']);
    $ram = intval($_POST['ram']);
    $storage = intval($_POST['storage']);
    $battery_capacity = intval($_POST['battery_capacity']);
    $used_months = intval($_POST['used_months']);
    $battery_health = intval($_POST['battery_health']);
    $price = floatval($_POST['price']);
    $description = trim($_POST['description']);

    // Validation: Used months cannot exceed phone age in months
    $current_year = date('Y');
    $age_months = ($current_year - $launch_year) * 12;
    if ($used_months > $age_months) {
        $error = "Used months cannot exceed phone age ($age_months months).";
    }

    // Validation: Battery health must be between 50-100%
    if ($battery_health < 50 || $battery_health > 100) {
        $error = "Battery health must be between 50% and 100%.";
    }

    // Handle new image upload
    $image = $listing['image']; // keep existing by default
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg','jpeg','png','webp'];
        if (in_array(strtolower($ext), $allowed)) {
            $new_name = uniqid('iphone_').'.'.$ext;
            move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/'.$new_name);
            // Delete old image if exists
            if ($listing['image'] && file_exists('uploads/'.$listing['image'])) {
                unlink('uploads/'.$listing['image']);
            }
            $image = $new_name;
        } else {
            $error = "Only JPG, PNG, WEBP images allowed.";
        }
    }

    if (!$error) {
        $stmt = $conn->prepare("UPDATE listings SET 
            model=?, 
            launch_year=?, 
            ram=?, 
            storage=?, 
            battery_capacity=?, 
            used_months=?, 
            battery_health=?, 
            price=?, 
            description=?, 
            image=? 
            WHERE id=? AND user_id=?");

        // Fix: Correct types string (s=string, i=int)
        $stmt->bind_param(
            "siiiiiiissii",
            $model,
            $launch_year,
            $ram,
            $storage,
            $battery_capacity,
            $used_months,
            $battery_health,
            $price,
            $description,
            $image,
            $listing_id,
            $user_id
        );

        if ($stmt->execute()) {
            $success = "Listing updated successfully!";
            // Refresh listing data
            $stmt2 = $conn->prepare("SELECT * FROM listings WHERE id=?");
            $stmt2->bind_param("i",$listing_id);
            $stmt2->execute();
            $listing = $stmt2->get_result()->fetch_assoc();
        } else {
            $error = "Failed to update listing.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Listing - Used iPhone Marketplace</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background-color: #f8f9fa; }
.img-preview { width: 150px; height: 150px; object-fit: cover; margin-bottom: 10px; }
</style>
</head>
<body>

<?php include('includes/header.php'); ?>

<div class="container mt-5">
    <h2 class="mb-4 text-center">Edit iPhone Listing</h2>

    <?php if($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card shadow p-4">
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">iPhone Model</label>
                <select name="model" class="form-select" required>
                    <?php
                    $models = ['iPhone 12','iPhone 12 Pro','iPhone 13','iPhone 13 Pro','iPhone 14','iPhone 14 Pro'];
                    foreach($models as $m){
                        $sel = ($listing['model']==$m) ? "selected" : "";
                        echo "<option value='$m' $sel>$m</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Launch Year</label>
                <input type="number" name="launch_year" class="form-control" min="2007" max="<?php echo date('Y'); ?>" value="<?php echo $listing['launch_year']; ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">RAM (GB)</label>
                <input type="number" name="ram" class="form-control" min="1" value="<?php echo $listing['ram']; ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Storage (GB)</label>
                <input type="number" name="storage" class="form-control" min="8" value="<?php echo $listing['storage']; ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Battery Capacity (mAh)</label>
                <input type="number" name="battery_capacity" class="form-control" value="<?php echo $listing['battery_capacity']; ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Used Months</label>
                <input type="number" name="used_months" class="form-control" value="<?php echo $listing['used_months']; ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Battery Health (%)</label>
                <input type="number" name="battery_health" class="form-control" min="50" max="100" value="<?php echo $listing['battery_health']; ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Price (NPR)</label>
                <input type="number" name="price" class="form-control" min="0" value="<?php echo $listing['price']; ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Description / Condition</label>
                <textarea name="description" class="form-control" rows="4" required><?php echo $listing['description']; ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Current Image</label><br>
                <?php if($listing['image'] && file_exists('uploads/'.$listing['image'])): ?>
                    <img src="uploads/<?php echo $listing['image']; ?>" class="img-preview" alt="iPhone Image">
                <?php endif; ?>
                <input type="file" name="image" class="form-control">
            </div>

            <button type="submit" name="update" class="btn btn-primary w-100">Update Listing</button>
        </form>
    </div>
</div>

<?php include('includes/footer.php'); ?>
</body>
</html>
