<?php
@include '../components/connect.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
session_start();

$admin_id = $_SESSION['admin_id'];
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

requireAdminLogin();

$admin = getCurrentAdmin();
$db = getDB();

$error = '';
$success = '';

// get admin data
$stmt = $db->prepare("SELECT * FROM admin WHERE id=?");
$stmt->execute([$admin['id']]);
$adminData = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF check
    // if (!verifyCsrf()) {
    //     $error = 'Invalid request!';
    // } else {

    $name = trim($_POST['name'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // VALIDATION
    if (empty($name)) {
        $error = 'Username is required!';
    } elseif (!empty($password) && strlen($password) < 6) {
        $error = 'Password must be at least 6 characters!';
    } elseif (!empty($password) && $password !== $confirm) {
        $error = 'Passwords do not match!';
    } else {

        // check duplicate username
        $check = $db->prepare("SELECT id FROM admin WHERE name=? AND id!=?");
        $check->execute([$name, $admin['id']]);

        if ($check->fetch()) {
            $error = 'Username already taken!';
        } else {
            // update query
            if (!empty($password)) {
                $update = $db->prepare("UPDATE admin SET name=?, password=? WHERE id=?");
                $update->execute([
                    $name,
                    ($password),
                    $admin['id']
                ]);
            } else {
                $update = $db->prepare("UPDATE admin SET name=? WHERE id=?");
                $update->execute([$name, $admin['id']]);
            }

            $_SESSION['admin_name'] = $name;
            $success = "Profile updated successfully! 🎉";

            // refresh data
            $adminData['name'] = $name;
        }
    }
    // }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/admin-Style.css">


</head>

<body>
    <?php include '../components/admin_header.php'; ?>

    <div class="row justify-content-center align-items-center h-100 my-5">
        <div class="col-lg-5">
            <div class="admin-form-card rounded-3 shadow-lg">
                <div class="card-header  text-white p-2 rounded-top-4" style="background:var(--primary);">
                    <h5 class="py-2 ps-3"><i class="fas fa-user-cog me-2"></i>Update Admin Account</h5>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <strong><?= sanitize($success) ?></strong>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?= sanitize($error) ?></div>
                    <?php endif; ?>

                    <div class="text-center mb-4 pt-3">
                        <div class="user-avatar mx-auto" style="width:64px;height:64px;font-size:1.8rem;background:var(--primary);"><?= strtoupper(substr($adminData['name'], 0, 1)) ?></div>
                        <h5 class="mt-2 mb-0"><?= sanitize($adminData['name']) ?></h5>
                        <small class="text-muted">Administrator</small>
                    </div>

                    <form method="POST" class="p-4">
                        <!-- <input type="hidden" name="csrf_token" value=""> -->
                        <div class="mb-3">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" value="<?= sanitize($adminData['name']) ?>" required>
                        </div>
                        <hr>
                        <p class="text-muted small mb-3"><i class="fas fa-info-circle me-1"></i>Leave password empty to keep current password.</p>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="password" placeholder="Min. 6 characters">
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" name="confirm_password" placeholder="Repeat new password">
                        </div>
                        <button type="submit" class="btn-primary-custom btn w-100"><i class="fas fa-save me-2"></i>Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>




    <!-- JS -->

    <script src="../bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script src="../js/adminScript.js"></script>


</body>

</html>