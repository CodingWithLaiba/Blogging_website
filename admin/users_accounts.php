<?php
@include '../components/connect.php';
session_start();

$admin_id = $_SESSION['admin_id'];
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// ================== HELPER FUNCTIONS ==================
function sanitize($data)
{
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function formatDate($date)
{
    return date("d M Y", strtotime($date));
}

// ================== DELETE USER ==================
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];

    // Delete user
    $delete_user = $conn->prepare("DELETE FROM users WHERE id = ?");
    $delete_user->execute([$delete_id]);

    header("Location: users_accounts.php");
    exit();
}

// ================== FETCH USERS ==================
$select_users = $conn->query("SELECT * FROM users ORDER BY id DESC");
$users = $select_users->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Account</title>

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/admin-Style.css">


</head>

<body>
    <?php include '../components/admin_header.php'; ?>
    <!-- ================== USERS TABLE ================== -->
    <div class="admin-table-card mt-4 me-3 shadow-lg">

        <!-- Header -->
        <div class="admin-table-header">
            <h5>
                <i class="fas fa-users me-2"></i>
                User Accounts (<?= count($users) ?>)
            </h5>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table admin-table mb-0">

                <thead style="background-color: aliceblue;">
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Joined</th>
                        <th>Activity</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                No users found.
                            </td>
                        </tr>
                    <?php else: ?>

                        <?php foreach ($users as $user): ?>

                            <?php
                            // Likes count
                            $likes_stmt = $conn->prepare("SELECT COUNT(*) FROM likes WHERE user_id = ?");
                            $likes_stmt->execute([$user['id']]);
                            $likes = $likes_stmt->fetchColumn();

                            // Comments count
                            $comments_stmt = $conn->prepare("SELECT COUNT(*) FROM comments WHERE user_id = ?");
                            $comments_stmt->execute([$user['id']]);
                            $comments = $comments_stmt->fetchColumn();
                            ?>

                            <tr>
                                <!-- ID -->
                                <td style="color:var(--text-muted);font-size:0.8rem;">
                                    <?= $user['id'] ?>
                                </td>

                                <!-- USER -->
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="user-avatar" style="width:28px;height:28px;font-size:0.7rem;">
                                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                        </div>
                                        <span style="font-size:0.85rem;font-weight:500;">
                                            <?= sanitize($user['name']) ?>
                                        </span>
                                    </div>
                                </td>

                                <!-- EMAIL -->
                                <td style="font-size:0.85rem;">
                                    <?= sanitize($user['email']) ?>
                                </td>

                                <!-- DATE -->
                                <td style="font-size:0.78rem;white-space:nowrap;">
                                    <?= isset($user['created_at'])
                                        ? formatDate($user['created_at'])
                                        : 'N/A'; ?>
                                </td>

                                <!-- ACTIVITY -->
                                <td style="font-size:0.8rem;">
                                    <i class="fas fa-heart text-danger"></i> <?= $likes ?>
                                    &nbsp;
                                    <i class="fas fa-comment" style="color:var(--primary);"></i> <?= $comments ?>
                                </td>

                                <!-- DELETE -->
                                <td>
                                    <a href="users_accounts.php?delete=<?= $user['id'] ?>"
                                        class="btn btn-sm"
                                        style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;font-size:0.75rem;padding:0.25rem 0.6rem;border-radius:4px;"
                                        onclick="return confirm('Are you sure to delete this user?');">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>
                </tbody>

            </table>
        </div>

    </div>

    <!-- JS -->

    <script src="../bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script src="../js/adminScript.js"></script>


</body>

</html>