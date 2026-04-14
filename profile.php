<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Make sure user is logged in
requireUserLogin();
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}
// Get current user
$user = getCurrentUser();
$db = getDB();

$error = '';

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // Validation
    if (empty($name) || empty($email)) {
        $error = 'Name and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email.';
    } elseif (!empty($password) && strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif (!empty($password) && $password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {

        // Check email already used by another user
        $check = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check->execute([$email, $user['id']]);

        if ($check->fetch()) {
            $error = 'This email is already in use.';
        } else {

            // If password provided → update with password
            if (!empty($password)) {

                $hashed = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $db->prepare("UPDATE users SET name=?, email=?, password=? WHERE id=?");
                $stmt->execute([$name, $email, $hashed, $user['id']]);
            } else {

                // Update without password
                $stmt = $db->prepare("UPDATE users SET name=?, email=? WHERE id=?");
                $stmt->execute([$name, $email, $user['id']]);
            }

            // Update session
            $_SESSION['user_id'];

            // Success message + redirect
            setFlash('success', 'Profile updated successfully!');
            header('Location: profile.php');
            exit;
        }
    }
}

// Use existing user data 
$userData = $user;

$categories = getAllCategories();
$pageTitle = 'My Profile';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">


</head>

<body>
    <?php include 'components/user_header.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="auth-card">
                    <div class="auth-card-header">
                        <div class="user-avatar mx-auto mb-2" style="width:60px;height:60px;font-size:1.5rem;"><?= strtoupper(substr($userData['name'], 0, 1)) ?></div>
                        <h2 style="font-size:1.3rem;">My Profile</h2>
                        <p style="color:rgba(255,255,255,0.65);font-size:0.85rem;">Update your account information</p>
                    </div>
                    <div class="auth-card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?= sanitize($error) ?></div>
                        <?php endif; ?>
                         <?php if ($error): ?>
                            <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?= sanitize($error) ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <input type="hidden">

                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" name="name" value="<?= sanitize($userData['name'] ?? '') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" name="email" value="<?= sanitize($userData['email'] ?? '') ?>" required>
                            </div>

                            <hr>
                            <p class="text-muted small mb-3"><i class="fas fa-info-circle me-1"></i>Leave password fields empty to keep your current password.</p>

                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" class="form-control" name="password" placeholder="Min. 6 characters (optional)">
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" name="confirm_password" placeholder="Repeat new password">
                            </div>

                            <div class="d-flex gap-3">
                                <button type="submit" class="btn-primary-custom btn"><i class="fas fa-save me-2"></i>Save Changes</button>
                                <a href="index.php" class="btn btn-outline-secondary" style="border-radius:25px;padding:0.65rem 1.5rem;">Cancel</a>
                            </div>
                        </form>

                        <div class="text-center mt-4 pt-3 border-top">
                            <a href="user_likes.php" class="text-decoration-none me-3"><i class="fas fa-heart me-1" style="color:var(--danger);"></i>My Liked Posts</a>
                            <a href="components/user_logout.php" class="text-danger text-decoration-none"><i class="fas fa-sign-out-alt me-1"></i>Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>




    <?php include 'components/footer.php'; ?>

    <script src="bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>

</html>