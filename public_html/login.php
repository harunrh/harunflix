<?php
require_once '../private/functions.php';
// Redirect if already logged in
if (is_logged_in()) {
    header("Location: index.php");
    exit();
}

$error = '';

// Process login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password']; // Don't sanitize password before verification
    
    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password";
    } else {
        if (login_user($username, $password)) {
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid username or password";
        }
    }
}

include 'templates/header.php';
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-5">
        <div class="text-center mb-4">
            <img src="images/harunflix.png" alt="HarunFlix Logo" style="height: 60px;" class="mb-3">
            <h2>Sign In</h2>
            <p class="text-muted">Welcome back to HarunFlix</p>
        </div>
        
        <div class="card">
            <div class="card-body p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form action="login.php" method="post">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control form-control-lg" id="username" name="username" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control form-control-lg" id="password" name="password" required>
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">Login</button>
                    </div>
                </form>
                <div class="mt-4 text-center">
                    <p>Don't have an account? <a href="register.php">Register here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>