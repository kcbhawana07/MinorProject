<?php
session_start();
include('includes/db_connect.php');

$error = '';
$success = '';
$message = '';

if(isset($_GET['message'])){
    $message = $_GET['message'];
}

if(isset($_POST['register'])){
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $password = $_POST['password']; // plain text

    // Validate email format
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $error = "Invalid email format!";
    } else {
        // Allowed domains
        $allowed_domains = ['gmail.com', 'yahoo.com', 'outlook.com', 'hotmail.com'];
        $domain = strtolower(substr(strrchr($email, "@"), 1));

        if(!in_array($domain, $allowed_domains)){
            $error = "Email domain not allowed!";
        } else {
            // Check if email already exists
            $check = $conn->query("SELECT * FROM users WHERE email='$email' LIMIT 1");
            if($check->num_rows > 0){
                $error = "Email already registered!";
            } else {
                // Insert new user
                $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, trust_score, created_at) VALUES (?, ?, ?, ?, 0, NOW())");
$stmt->bind_param("ssss", $name, $email, $phone, $password);
                if($stmt->execute()){
                    $success = "Registration successful! You can now login.";
                } else {
                    $error = "Registration failed. Try again.";
                }
            }
        }
    }
}
?>


<?php include('includes/header.php'); ?>

<div class="container mt-5" style="max-width: 500px;">
    <h3 class="text-center mb-4">User Registration</h3>

    <?php if($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if($message): ?>
        <div class="alert alert-info"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" placeholder="Your name" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" placeholder="Your email" required>
        </div>

          <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="tel" name="phone" class="form-control" placeholder="Your Phone Number" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>
        <button type="submit" name="register" class="btn btn-success w-100">Register</button>
    </form>

    <p class="mt-3 text-center">Already have an account? <a href="login_user.php">Login here</a></p>
</div>

<?php include('includes/footer.php'); ?>
