<?php
@include '../components/connect.php';
session_start();

// 🔥 Prevent redirect loop
if (isset($_SESSION['admin_id'])) {
    header('location:dashboard.php');
    exit();
}

$error = '';

function sanitize($data)
{
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

if (isset($_POST['submit'])) {

    $name = trim($_POST['username']);
    $pass = trim($_POST['password']);

    if (empty($name) || empty($pass)) {
        $error = "Please fill all fields!";
    } else {

        $stmt = $conn->prepare("SELECT * FROM admin WHERE name = ?");
        $stmt->execute([$name]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin) {

            if ($pass == $admin['password']) {

                // ✅ SET SESSION
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['name'];

                // 🔥 DEBUG (temporary)
                // print_r($_SESSION); exit();

                header('location:dashboard.php');
                exit();
            } else {
                $error = "Wrong password!";
            }
        } else {
            $error = "Admin not found!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/adminStyle.css">

    <style>
        body {
            background: linear-gradient(135deg, #0f2340 0%, #1e3a5f 50%, #2d5282 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="text-center mb-4">
                    <div style="font-family:'Playfair Display',serif;font-size:2rem;color:#fff;">Blog<span style="color:var(--accent);">Sphere</span></div>
                </div>
                <div class="auth-card">
                    <div class="auth-card-header" style="background:linear-gradient(135deg,rgba(15,35,64,0.8),rgba(30,58,95,0.8));backdrop-filter:blur(10px);">
                        <i class="fas fa-shield-alt" style="font-size:2.5rem;color:var(--accent);margin-bottom:0.75rem;display:block;"></i>
                        <h2 style="font-size:1.2rem;">Admin Panel</h2>
                        <p style="color:rgba(255,255,255,0.6);font-size:0.82rem;margin-top:0.25rem;">Restricted Access Only</p>
                    </div>
                    <div class="auth-card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?= sanitize($error) ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background:var(--bg-light);border:1.5px solid var(--border);border-right:none;"><i class="fas fa-user" style="color:var(--text-muted);font-size:0.875rem;"></i></span>
                                    <input type="text" class="form-control" name="username" placeholder="Admin username" value="<?= isset($_POST['username']) ? sanitize($_POST['username']) : '' ?>" required style="border-left:none;">
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background:var(--bg-light);border:1.5px solid var(--border);border-right:none;"><i class="fas fa-lock" style="color:var(--text-muted);font-size:0.875rem;"></i></span>
                                    <input type="password" class="form-control" name="password" placeholder="Admin password" required style="border-left:none;">
                                </div>
                            </div>
                            <button type="submit" name="submit" class="btn-primary-custom btn w-100">
                                <i class="fas fa-sign-in-alt me-2"></i>Sign In
                            </button>
                        </form>
                        <div class="text-center mt-4">
                            <a href="../home.php" style="font-size:0.82rem;color:var(--text-muted);"><i class="fas fa-arrow-left me-1"></i>Back to Website</a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>





    <!-- JS -->

    <script src="../bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/adminScript.js"></script>


</body>

</html>