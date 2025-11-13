<?php
session_start();
include('../includes/db_connect.php');

$error = '';

if(isset($_POST['login'])){
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password']; // plain text for now

    $query = "SELECT * FROM admin WHERE username='$username' AND password='$password' LIMIT 1";
    $result = $conn->query($query);

    if($result->num_rows == 1){
        $admin = $result->fetch_assoc();
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['role'] = 'admin';

        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>

<?php include('../includes/header.php'); ?>

<div class="container mt-5" style="max-width: 400px;">
    <h3 class="text-center mb-4">Admin Login</h3>

    <?php if($error): ?>
        <div class="alert alert-danger text-center"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" placeholder="Enter username" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Enter password" required>
        </div>

        <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
    </form>
</div>

<?php include('../includes/footer.php'); ?>
