<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// If already logged in → go to home
if (isUserLoggedIn()) {
    header('Location: home.php');
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
                header('Location: home.php');
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

        <!-- LEFT PANEL -->
        <div class="auth-left-panel">
            <div class="auth-left-inner">

                <a href="home.php" class="auth-panel-logo">
                    Blog<span>Sphere</span>
                </a>

                <div class="auth-panel-content">
                    <h2 class="auth-panel-heading">Welcome Back</h2>
                    <p class="auth-panel-sub">
                        Login to continue reading and sharing blogs.
                    </p>
                </div>

            </div>
        </div>

        <!-- RIGHT PANEL -->
        <div class="auth-right-panel">
            <div class="auth-right-inner">

                <div class="auth-form-header">
                    <h3>Sign In</h3>
                    <p>Enter your credentials</p>
                </div>

                <!-- ERROR -->
                <?php if ($error): ?>
                    <div class="auth-alert auth-alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= sanitize($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">

                    <!-- Email -->
                    <div class="auth-field">
                        <label>Email</label>
                        <div class="auth-input-wrap">
                            <i class="fas fa-envelope auth-input-icon"></i>
                            <input type="email" name="email" class="auth-input"
                                value="<?= sanitize($_POST['email'] ?? '') ?>"
                                placeholder="Enter email" required>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="auth-field">
                        <label>Password</label>
                        <div class="auth-input-wrap">
                            <i class="fas fa-lock auth-input-icon"></i>
                            <input type="password" name="password" id="loginPass"
                                class="auth-input" placeholder="Enter password" required>

                            <button type="button" class="auth-eye-btn"
                                onclick="togglePass('loginPass', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Button -->
                    <button type="submit" class="auth-submit-btn">
                        <i class="fas fa-sign-in-alt me-2"></i> Login
                    </button>

                </form>

                <!-- Switch -->
                <div class="auth-switch">
                    Don't have an account?
                    <a href="register.php">Register</a>
                </div>

                <!-- Back -->
                <div class="auth-back-link">
                    <a href="home.php">
                        <i class="fas fa-arrow-left"></i> Back to Home
                    </a>
                </div>

            </div>
        </div>

    </div>

    <script>
        function togglePass(id, btn) {
            const input = document.getElementById(id);
            const icon = btn.querySelector('i');

            if (input.type === "password") {
                input.type = "text";
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = "password";
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>

</body>

</html>