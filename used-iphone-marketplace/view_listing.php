<?php
include('includes/db_connect.php');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: browse.php");
    exit();
}

$listing_id = intval($_GET['id']);

// Fetch listing and seller info
$stmt = $conn->prepare("
    SELECT l.*, u.name AS seller_name, u.email AS seller_email, u.trust_score,u.phone AS seller_phone
    FROM listings l
    JOIN users u ON l.user_id = u.id
    WHERE l.id = ?
");
$stmt->bind_param("i", $listing_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "Listing not found!";
    exit();
}

$listing = $result->fetch_assoc();

// Function to get trust badge
function getBadge($score) {
    if ($score >= 20) return "🏆 Super Seller";
    if ($score >= 10) return "💎 Verified Seller";
    if ($score >= 3)  return "⭐ Trusted Seller";
    return "🆕 New Seller";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($listing['model']); ?> - Details</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.img-large { width: 100%; max-height: 400px; object-fit: cover; }
</style>
</head>
<body>

<?php include('includes/header.php'); ?>

<div class="container mt-5">
    <div class="row">
        <!-- Listing Image -->
        <div class="col-md-6">
            <?php if($listing['image'] && file_exists('uploads/'.$listing['image'])): ?>
                <img src="uploads/<?php echo $listing['image']; ?>" class="img-large rounded shadow-sm" alt="iPhone Image">
            <?php else: ?>
                <img src="assets/img/placeholder.png" class="img-large rounded shadow-sm" alt="No Image">
            <?php endif; ?>
        </div>

        <!-- Listing Details -->
        <div class="col-md-6">
            <h2><?php echo htmlspecialchars($listing['model']); ?> (<?php echo $listing['launch_year']; ?>)</h2>
            <p><strong>Price:</strong> NPR <?php echo number_format($listing['price']); ?></p>
            <p><strong>RAM:</strong> <?php echo $listing['ram']; ?> GB</p>
            <p><strong>Storage:</strong> <?php echo $listing['storage']; ?> GB</p>
            <p><strong>Battery Capacity:</strong> <?php echo $listing['battery_capacity']; ?> mAh</p>
            <p><strong>Used Months:</strong> <?php echo $listing['used_months']; ?></p>
            <p><strong>Battery Health:</strong> <?php echo $listing['battery_health']; ?>%</p>
            <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($listing['description'])); ?></p>

            <hr>

            <h5>Seller Information</h5>
            <p>
                <strong>Name:</strong> <?php echo htmlspecialchars($listing['seller_name']); ?> <br>
                <strong>Email:</strong> <?php echo htmlspecialchars($listing['seller_email']); ?> <br>
                <strong>Phone:</strong> <?php echo htmlspecialchars($listing['seller_phone']); ?> <br>
                <strong>Total Successful Sales:</strong> <?php echo $listing['trust_score']; ?> <br>
                <span class="badge bg-success"><?php echo getBadge($listing['trust_score']); ?></span>
            </p>

            <!-- Contact Seller Button -->
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#contactModal">
                Email Seller
            </button>
        </div>
    </div>
</div>

<!-- Contact Modal -->
<div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?php echo htmlspecialchars($listing['seller_name']); ?> - Contact</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Email: <a href="mailto:<?php echo htmlspecialchars($listing['seller_email']); ?>"><?php echo htmlspecialchars($listing['seller_email']); ?></a></p>
        <p>You can contact the seller directly via email to negotiate or ask questions.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include('includes/footer.php'); ?>
</body>
</html>
