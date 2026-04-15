<?php
@include '../components/connect.php';
session_start();

$error = '';
$success = '';

function sanitize($data)
{
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

if (isset($_POST['submit'])) {

    $name = trim($_POST['username']);
    $pass = trim($_POST['password']);
    $cpass = trim($_POST['confirm_password']);

    if (empty($name) || empty($pass) || empty($cpass)) {
        $error = "Please fill all fields!";
    } elseif ($pass != $cpass) {
        $error = "Passwords do not match!";
    } else {
        // check existing admin
        $check = $conn->prepare("SELECT * FROM admin WHERE name = ?");
        $check->execute([$name]);

        if ($check->rowCount() > 0) {
            $error = "Admin already exists!";
        } else {
            // insert new admin (simple password)
            $insert = $conn->prepare("INSERT INTO admin(name, password) VALUES(?, ?)");
            $insert->execute([$name, $pass]);

            $success = "Admin registered successfully!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Admin</title>

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">


</head>

<body>
    <?php include '../components/admin_header.php'; ?>


    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">

                <!-- Logo -->
                <div class="text-center mb-4">
                    <h2 style="color:blue;">Blog<span style="color:var(--accent);;">Sphere</span></h2>
                </div>

                <div class="auth-card">

                    <div class="auth-card-header text-center p-3"
                        style="background: linear-gradient(135deg, var(--primary-dark), var(--primary)); color:#fff;">
                        <i class="fas fa-user-plus fa-2x mb-2"></i>
                        <h5>Create New Admin</h5>
                    </div>

                    <div class="auth-card-body p-4 bg-white">

                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= sanitize($error) ?></div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>

                        <form method="POST">

                            <div class="mb-3">
                                <label>Username</label>
                                <input type="text" name="username" class="form-control"
                                    placeholder="Enter username"
                                    value="<?= sanitize($_POST['username'] ?? '') ?>">
                            </div>

                            <div class="mb-3">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control"
                                    placeholder="Enter password">
                            </div>

                            <div class="mb-3">
                                <label>Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control"
                                    placeholder="Confirm password">
                            </div>

                            <button type="submit" name="submit" class="btn btn-primary w-100">
                                <i class="fas fa-user-plus"></i> Register
                            </button>

                        </form>

                        <div class="text-center mt-3">
                            <a href="admin_login.php">Already have account? Login</a>
                        </div>

                    </div>
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