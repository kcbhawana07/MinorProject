<?php
include('includes/header.php');

if(isset($_POST['send'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);

    // Here you could save the message to DB or send email
    // For now, just show success alert
    $success = "Thank you, $name! Your message has been sent successfully.";
}
?>

<div class="container mt-5">
    <h2 class="mb-4 text-center">Contact Us</h2>

    <?php if(isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Your Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Message</label>
                    <textarea name="message" class="form-control" rows="5" required></textarea>
                </div>
                <button type="submit" name="send" class="btn btn-primary w-100">Send Message</button>
            </form>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
