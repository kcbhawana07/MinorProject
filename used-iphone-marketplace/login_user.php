<?php
session_start();
include('includes/db_connect.php');

$error = '';

if(isset($_POST['login'])){
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password']; // plain text

    $query = "SELECT * FROM users WHERE email='$email' AND password='$password' LIMIT 1";
    $result = $conn->query($query);

    if($result->num_rows == 1){
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        
        $_SESSION['role'] = 'user';

        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid email or password!";
    }
}
?>

<?php include('includes/header.php'); ?>

<div class="container mt-5" style="max-width: 400px;">
    <h3 class="text-center mb-4">User Login</h3>

    <?php if($error): ?>
        <div class="alert alert-danger text-center"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
        </div>

          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Enter password" required>
        </div>

        <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
    </form>

    <p class="mt-3 text-center">Don’t have an account? <a href="register.php">Register here</a></p>
</div>

<?php include('includes/footer.php'); ?>
