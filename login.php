<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// If already logged in → go to home
if (isUserLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';

// Run when form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    if (empty($email) || empty($password)) {
        $error = "Please fill all fields!";
    } else {

        $db = getDB();

        // Check user
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {

            // If you are using plain password (like your register page)
            if ($password == $user['password']) {

                //loginUser($user); // store session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                header('Location: index.php');
                exit();
            } else {
                $error = "Wrong password!";
            }
        } else {
            $error = "User not found!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

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
                <h2 class="auth-panel-heading">Welcome<br>Back!</h2>
                <p class="auth-panel-sub">Sign in and continue your journey through stories, ideas, and knowledge.</p>

                <div class="auth-panel-features">
                    <div class="auth-feature-item">
                        <div class="auth-feature-icon"><i class="fas fa-pen-fancy"></i></div>
                        <div>
                            <strong>Publish Your Stories</strong>
                            <span>Share your voice with the world</span>
                        </div>
                    </div>
                    <div class="auth-feature-item">
                        <div class="auth-feature-icon"><i class="fas fa-heart"></i></div>
                        <div>
                            <strong>Like &amp; Save Posts</strong>
                            <span>Curate your favourite reads</span>
                        </div>
                    </div>
                    <div class="auth-feature-item">
                        <div class="auth-feature-icon"><i class="fas fa-comments"></i></div>
                        <div>
                            <strong>Join the Discussion</strong>
                            <span>Comment and engage with authors</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="auth-panel-quote">
                <i class="fas fa-quote-left"></i>
                <p>"The more that you read, the more things you will know."</p>
                <span>— Dr. Seuss</span>
            </div>
        </div>
    </div>

    <!-- Right Panel (Form) -->
    <div class="auth-right-panel">
        <div class="auth-right-inner">
            <div class="auth-form-header">
                <h3>Sign In</h3>
                <p>Enter your credentials to access your account</p>
            </div>

            <?php if ($error): ?>
            <div class="auth-alert auth-alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?= sanitize($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" id="loginForm">
                <input type="hidden">
                <input type="hidden" name="redirect" value="<?= sanitize($redirect) ?>">

                <div class="auth-field">
                    <label>Email Address</label>
                    <div class="auth-input-wrap">
                        <i class="fas fa-envelope auth-input-icon"></i>
                        <input type="email" name="email" class="auth-input" placeholder="you@example.com" value="<?= sanitize($_POST['email'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="auth-field">
                    <label>Password</label>
                    <div class="auth-input-wrap">
                        <i class="fas fa-lock auth-input-icon"></i>
                        <input type="password" name="password" class="auth-input" id="loginPassword" placeholder="Enter your password" required>
                        <button type="button" class="auth-eye-btn" onclick="togglePass('loginPassword', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="auth-submit-btn">
                    <i class="fas fa-sign-in-alt me-2"></i>Sign In to BlogSphere
                </button>
            </form>

            <div class="auth-divider"><span>or</span></div>

            <div class="auth-switch">
                Don't have an account?
                <a href="register.php">Create one free <i class="fas fa-arrow-right ms-1"></i></a>
            </div>

            <div class="auth-back-link">
                <a href="index.php"><i class="fas fa-arrow-left me-1"></i>Back to BlogSphere</a>
            </div>
        </div>
    </div>
</div>


    <script src="bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>

</html>