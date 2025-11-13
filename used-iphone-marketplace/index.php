<?php
session_start();
include('includes/db_connect.php');

// Fetch latest 4 available listings
$featured = $conn->query("
    SELECT l.*, u.name AS seller_name
    FROM listings l
    JOIN users u ON l.user_id = u.id
    WHERE l.status='Available'
    ORDER BY l.created_at DESC
    LIMIT 4
");
?>

<?php include('includes/header.php'); ?>

<!-- ✅ Logout Message -->
<?php if (isset($_GET['logout'])): ?>
<div class="alert alert-success text-center mt-3" style="max-width:600px; margin:auto;">
    ✅ You have been logged out successfully!
</div>
<?php endif; ?>

<!-- ✅ Hero Section with Background Image -->
<section class="hero mb-5 text-center text-white d-flex align-items-center justify-content-center" 
    style="
        background: 
            linear-gradient(135deg, rgba(0,123,255,0.75), rgba(102,16,242,0.75)), 
            url('assets/img/iphone_1.jpg') center/cover no-repeat;
        height: 50vh;
    ">
    <div class="container">
        <h1 class="display-4 fw-bold">Your trusted marketplace for second-hand iPhones</h1>
        <p class="lead mb-4">Browse listings or sell your own iPhone securely and quickly.</p>
        <a href="browse.php" class="btn btn-light btn-lg me-2">Browse iPhones</a>
        <a href="sell.php" class="btn btn-outline-light btn-lg">List Your iPhone</a>
    </div>
</section>


<!-- ✅ Featured Listings -->
<div class="container mb-5">
    <h2 class="mb-4 text-center">Featured iPhones</h2>
    <div class="row">
        <?php if($featured->num_rows > 0): ?>
            <?php while($row = $featured->fetch_assoc()): ?>
            <div class="col-md-3 mb-4">
                <div class="card shadow-sm h-100">
                    <?php if($row['image'] && file_exists('uploads/'.$row['image'])): ?>
                        <img src="uploads/<?php echo $row['image']; ?>" class="card-img-top" alt="iPhone Image">
                    <?php else: ?>
                        <img src="assets/img/placeholder.png" class="card-img-top" alt="No Image Available">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title text-truncate"><?php echo htmlspecialchars($row['model']); ?></h5>
                        <p class="card-text small">
                            <strong>Price:</strong> NPR <?php echo number_format($row['price']); ?><br>
                            <strong>Seller:</strong> <?php echo htmlspecialchars($row['seller_name']); ?>
                        </p>
                        <a href="view_listing.php?id=<?php echo $row['id']; ?>" class="btn btn-primary w-100">View Details</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-center text-muted">No listings available right now.</p>
        <?php endif; ?>
    </div>
</div>

<?php include('includes/footer.php'); ?>
