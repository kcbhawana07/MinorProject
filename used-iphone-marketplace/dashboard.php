<?php
session_start();
include('includes/db_connect.php');

// Block access if not logged in or not a user
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user'){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// --------------------
// Delete listing
// --------------------
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM listings WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: dashboard.php");
    exit();
}

// --------------------
// Mark as Sold
// --------------------
if(isset($_GET['sold'])){
    $id = intval($_GET['sold']);
    $stmt = $conn->prepare("UPDATE listings SET status='Sold' WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $stmt->close();

    // Update trust score
    $stmt = $conn->prepare("UPDATE users SET trust_score = trust_score + 1 WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    header("Location: dashboard.php");
    exit();
}

// --------------------
// Fetch user's listings
// --------------------
$stmt = $conn->prepare("SELECT * FROM listings WHERE user_id=? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$listings = $stmt->get_result();
?>

<?php include('includes/header.php'); ?>

<div class="container mt-5">
    <h2 class="mb-4 text-center">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h2>
    <a href="sell.php" class="btn btn-success mb-3">List New iPhone</a>

    <?php if($listings->num_rows > 0): ?>
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Model</th>
                    <th>Price (NPR)</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $listings->fetch_assoc()): ?>
                <tr>
                    <td style="width:80px;">
                        <?php if($row['image'] && file_exists('uploads/'.$row['image'])): ?>
                            <img src="uploads/<?php echo $row['image']; ?>" alt="iPhone" class="img-fluid">
                        <?php else: ?>
                            <img src="assets/img/placeholder.png" alt="No Image" class="img-fluid">
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['model']); ?></td>
                    <td><?php echo number_format($row['price']); ?></td>
                    <td><?php echo $row['status']; ?> <?php if($row['status']=='Sold') echo "✅"; ?></td>
                    <td>
                        <a href="edit_listing.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary mb-1">Edit</a>
                        <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger mb-1" onclick="return confirm('Delete this listing?')">Delete</a>
                        <?php if($row['status']=='Available'): ?>
                            <a href="?sold=<?php echo $row['id']; ?>" class="btn btn-sm btn-success mb-1" onclick="return confirm('Mark as sold?')">Mark Sold</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <p class="text-center text-muted">You have no listings yet. <a href="sell.php">List your first iPhone</a></p>
    <?php endif; ?>
</div>

<?php include('includes/footer.php'); ?>
