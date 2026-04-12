<?php

//  Connect database
@include '../components/connect.php';

//  Include auth + helper functions
require_once '../includes/auth.php';
require_once '../includes/functions.php';


// 🔹 Get logged-in admin ID from session
$admin_id = $_SESSION['admin_id'];

//  If not logged in → redirect to login page
if (!isset($admin_id)) {
    header('location:admin_login.php');
}

// Extra security check (auth.php function)
requireAdminLogin();

//  Get current logged-in admin details
$currentAdmin = getCurrentAdmin();


// 🔹 FETCH ALL ADMINS FROM DATABASE
$stmt = $conn->query("SELECT * FROM admin ORDER BY id DESC");
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);


// 🔹 DELETE ADMIN (simple logic)
if (isset($_GET['delete'])) {

    // get admin id from URL
    $delete_id = $_GET['delete'];

    // prevent deleting yourself
    if ($delete_id != $currentAdmin['id']) {

        // delete admin from DB
        $stmt = $conn->prepare("DELETE FROM admin WHERE id = ?");
        $stmt->execute([$delete_id]);
    }

    // reload page after delete
    header("Location: admin_accounts.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin A</title>

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/adminStyle.css">


</head>

<body>
    <?php include '../components/admin_header.php'; ?>


    <div class="container mt-4">
        <div class=" mx-auto">
            <div class="admin-table-card">

                <div class="admin-table-header">
                    <h5>
                        <i class="fas fa-user-shield me-2"></i>
                        Admin Accounts (<?= count($admins) ?>)
                    </h5>
                </div>

                <div class="table-responsive">
                    <table class="table admin-table mb-0">
                        <!-- Table Head -->

                        <thead style="background-color: aliceblue;">

                            <tr>
                                <th>#</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <!-- if no admin -->
                            <?php if (empty($admins)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        No admins found
                                    </td>
                                </tr>
                            <?php else: ?>

                                <?php foreach ($admins as $a): ?>
                                    <tr>
                                        <!-- Admin ID -->
                                        <td><?= $a['id'] ?></td>

                                        <td>
                                            <div class="d-flex align-items-center gap-2">

                                                <!-- Avatar -->
                                                <div class="user-avatar" style="width:30px;height:30px;">
                                                    <?= strtoupper(substr($a['name'], 0, 1)) ?>
                                                </div>
                                                <!--  Admin Name -->
                                                <span><?= sanitize($a['name']) ?></span>

                                                <!-- Current user -->
                                                <?php if ($a['id'] == $currentAdmin['id']): ?>
                                                    <span class="badge bg-primary">You</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <!--  Role -->

                                        <td>
                                            <span class="badge bg-success">Administrator</span>
                                        </td>
                                        <!--  Actions -- -->
                                        <td>
                                            <!-- If NOT current admin → allow delete -->

                                            <?php if ($a['id'] != $currentAdmin['id']): ?>
                                                <a href="?delete=<?= $a['id'] ?>"
                                                    class="btn btn-sm btn-danger btn-delete-confirm"
                                                    data-name="<?= sanitize($a['name']) ?>">
                                                    <i class="fas fa-trash"></i> Remove
                                                </a>
                                                <!-- Protect current admin -->

                                            <?php else: ?>
                                                <span class="text-muted small p-2 rounded-3 bg-warning">Protected</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                            <?php endif; ?>
                        </tbody>

                    </table>
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