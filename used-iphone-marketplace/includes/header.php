<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Determine correct path for assets depending on folder (root or admin)
$pathPrefix = '';
if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) {
    $pathPrefix = '../';
}
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

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?php echo $pathPrefix; ?>index.php">SmartResell</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="<?php echo $pathPrefix; ?>index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo $pathPrefix; ?>browse.php">Browse iPhones</a></li>

        <!-- Dynamic List Your iPhone link -->
        <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'user'): ?>
            <li class="nav-item"><a class="nav-link" href="<?php echo $pathPrefix; ?>sell.php">List Your iPhone</a></li>
        <?php else: ?>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo $pathPrefix; ?>register.php?message=<?php echo urlencode('Please register first to list your iPhone'); ?>">List Your iPhone</a>
            </li>
        <?php endif; ?>

        <li class="nav-item"><a class="nav-link" href="<?php echo $pathPrefix; ?>about.php">About</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo $pathPrefix; ?>contact.php">Contact</a></li>

       <!-- ✅ User Logged In -->
  <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'user'): ?>
      <li class="nav-item"><a class="nav-link" href="<?php echo $pathPrefix; ?>dashboard.php">My Listings</a></li>
      <li class="nav-item"><a class="nav-link" href="<?php echo $pathPrefix; ?>logout.php">Logout</a></li>

  <!-- ✅ Admin Logged In -->
  <?php elseif(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
      <li class="nav-item"><a class="nav-link" href="<?php echo $pathPrefix; ?>admin/admin_dashboard.php">Admin Dashboard</a></li>
      <li class="nav-item"><a class="nav-link" href="<?php echo $pathPrefix; ?>logout.php">Logout</a></li>

  <!-- ✅ No one logged in -->
  <?php else: ?>
      <li class="nav-item"><a class="nav-link" href="<?php echo $pathPrefix; ?>login_user.php">Login</a></li>
      <!-- <li class="nav-item"><a class="nav-link" href="<?php echo $pathPrefix; ?>admin/admin_login.php">Admin Login</a></li> -->
  <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
