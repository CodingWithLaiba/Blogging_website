<?php
// Include authentication and helper functions
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// If user already logged in, redirect to home page
if (isUserLoggedIn()) {
    header('Location: home.php');
    exit;
}

// Initialize messages
$error = '';
$success = '';

// Run when form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get form input values
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // Validation checks
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'All fields are required!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email!';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters!';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match!';
    } else {

        // Get database connection
        $db = getDB();

        // Check if email already exists in database
        $check = $db->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);

        if ($check->fetch()) {
            $error = 'Email already registered!';
        } else {

            // Insert new user into database
            $stmt = $db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $password]);

            // Get last inserted user ID
            $userId = $db->lastInsertId();

            // Start session login
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_name'] = $name;

            // Redirect to home page
            header("Location: home.php");
            exit;

            // Success message
            $success = "Account created successfully! You can now <a href='login.php' style = 'text-decoration:underline; color:#065f46'>login</a>.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register User</title>

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/auth.css">


</head>

<body class="auth-body">
    <div class="auth-page-wrapper">
        <!-- Left Panel -->
        <div class="auth-left-panel">
            <div class="auth-left-inner">
                <div class="auth-panel-deco deco-1"></div>
                <div class="auth-panel-deco deco-2"></div>
                <div class="auth-panel-deco deco-3"></div>
                <div class="auth-panel-deco deco-4"></div>

                <a href="index.php" class="auth-panel-logo">Blog<span>Sphere</span></a>

                <div class="auth-panel-content">
                    <h2 class="auth-panel-heading">Join Our<br>Community</h2>
                    <p class="auth-panel-sub">Create a free account and start reading, writing, and connecting with thousands of passionate bloggers.</p>

                    <div class="auth-stats-row">
                        <div class="auth-stat-box">
                            <span class="auth-stat-num">5K+</span>
                            <span class="auth-stat-lbl">Articles</span>
                        </div>
                        <div class="auth-stat-box">
                            <span class="auth-stat-num">2K+</span>
                            <span class="auth-stat-lbl">Writers</span>
                        </div>
                        <div class="auth-stat-box">
                            <span class="auth-stat-num">10K+</span>
                            <span class="auth-stat-lbl">Readers</span>
                        </div>
                    </div>

                    <div class="auth-panel-features">
                        <div class="auth-feature-item">
                            <div class="auth-feature-icon"><i class="fas fa-check"></i></div>
                            <div>
                                <strong>Free Forever</strong>
                                <span>No credit card required</span>
                            </div>
                        </div>
                        <div class="auth-feature-item">
                            <div class="auth-feature-icon"><i class="fas fa-shield-alt"></i></div>
                            <div>
                                <strong>Private &amp; Secure</strong>
                                <span>Your data is always safe</span>
                            </div>
                        </div>
                        <div class="auth-feature-item">
                            <div class="auth-feature-icon"><i class="fas fa-bolt"></i></div>
                            <div>
                                <strong>Instant Access</strong>
                                <span>Start reading immediately</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel (Form) -->
        <div class="auth-right-panel">
            <div class="auth-right-inner">
                <div class="auth-form-header">
                    <h3>Create Account</h3>
                    <p>Fill in your details to get started</p>
                </div>

                <?php if ($error): ?>
                    <div class="auth-alert auth-alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= $error ?>
                    </div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="auth-alert auth-alert-success">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= $success ?>
                    </div>
                <?php endif; ?>
                <form method="POST" id="registerForm">
                    <input type="hidden" name="csrf_token">

                    <div class="auth-fields-row">
                        <div class="auth-field">
                            <label>Full Name <span class="req">*</span></label>
                            <div class="auth-input-wrap">
                                <i class="fas fa-user auth-input-icon"></i>
                                <input type="text" name="name" class="auth-input" placeholder="Your full name" value="<?= sanitize($_POST['name'] ?? '') ?>" required minlength="2">
                            </div>
                        </div>
                        <div class="auth-field">
                            <label>Email Address <span class="req">*</span></label>
                            <div class="auth-input-wrap">
                                <i class="fas fa-envelope auth-input-icon"></i>
                                <input type="email" name="email" class="auth-input" placeholder="you@example.com" value="<?= sanitize($_POST['email'] ?? '') ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="auth-fields-row">
                        <div class="auth-field">
                            <label>Password <span class="req">*</span></label>
                            <div class="auth-input-wrap">
                                <i class="fas fa-lock auth-input-icon"></i>
                                <input type="password" name="password" class="auth-input" id="regPassword" placeholder="Min. 6 characters" required minlength="6">
                                <button type="button" class="auth-eye-btn" onclick="togglePass('regPassword', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="auth-field">
                            <label>Confirm Password <span class="req">*</span></label>
                            <div class="auth-input-wrap">
                                <i class="fas fa-lock auth-input-icon"></i>
                                <input type="password" name="confirm_password" class="auth-input" id="regConfirm" placeholder="Repeat password" required>
                                <button type="button" class="auth-eye-btn" onclick="togglePass('regConfirm', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>



                    <div class="auth-terms">
                        <input type="checkbox" id="terms" required>
                        <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
                    </div>

                    <button type="submit" class="auth-submit-btn auth-submit-accent">
                        <i class="fas fa-user-plus me-2"></i>Create Account
                    </button>
                </form>

                <div class="auth-divider"><span>or</span></div>

                <div class="auth-switch">
                    Already have an account?
                    <a href="login.php">Sign in <i class="fas fa-arrow-right ms-1"></i></a>
                </div>

                <div class="auth-back-link">
                    <a href="home.php"><i class="fas fa-arrow-left me-1"></i>Back to BlogSphere</a>
                </div>
            </div>
        </div>
    </div>



    <script src="bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>

</html>