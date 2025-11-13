<?php
session_start();
include('../includes/db_connect.php');

// ------------------------
// Block access if not logged in as admin
// ------------------------
if(!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

// ------------------------
// Delete a listing
// ------------------------
if(isset($_GET['delete_listing'])){
    $id = intval($_GET['delete_listing']);
    $stmt = $conn->prepare("DELETE FROM listings WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_dashboard.php");
    exit();
}

// ------------------------
// Ban/Delete a user and their listings
// ------------------------
if(isset($_GET['ban_user'])){
    $id = intval($_GET['ban_user']);

    // Delete user's listings
    $stmt = $conn->prepare("DELETE FROM listings WHERE user_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // Delete user
    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_dashboard.php");
    exit();
}

// ------------------------
// Fetch all users
// ------------------------
$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");

// ------------------------
// Fetch all listings with seller info
// ------------------------
$listings = $conn->query("
    SELECT l.*, u.name AS seller_name, u.email AS seller_email 
    FROM listings l 
    JOIN users u ON l.user_id = u.id 
    ORDER BY l.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Used iPhone Marketplace</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link href="<?php echo $pathPrefix; ?>assets/css/style.css" rel="stylesheet">
</head>
<body>




<div class="container mt-5">
    <h2 class="mb-4 text-center">Admin Dashboard</h2>
    <p class="text-center">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?> | 
        <a href="../logout.php" class="btn btn-danger btn-sm">Logout</a>
    </p>

    <!-- Users Section -->
    <h4 class="mt-4">All Users</h4>
    <?php if($users->num_rows > 0): ?>
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Trust Score</th>
                    <th>Joined At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($user = $users->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo $user['trust_score']; ?></td>
                    <td><?php echo $user['created_at']; ?></td>
                    <td>
                        <a href="?ban_user=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Ban this user and delete their listings?')">Ban/Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <p class="text-center text-muted">No users found.</p>
    <?php endif; ?>

    <!-- Listings Section -->
    <h4 class="mt-5">All Listings</h4>
    <?php if($listings->num_rows > 0): ?>
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Model</th>
                    <th>Price (NPR)</th>
                    <th>Status</th>
                    <th>Seller</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($listing = $listings->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $listing['id']; ?></td>
                    <td><?php echo htmlspecialchars($listing['model']); ?></td>
                    <td><?php echo number_format($listing['price']); ?></td>
                    <td><?php echo $listing['status']; ?></td>
                    <td><?php echo htmlspecialchars($listing['seller_name']); ?> (<?php echo htmlspecialchars($listing['seller_email']); ?>)</td>
                    <td>
                        <a href="?delete_listing=<?php echo $listing['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this listing?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <p class="text-center text-muted">No listings found.</p>
    <?php endif; ?>
</div>

<?php include('../includes/footer.php'); ?>
