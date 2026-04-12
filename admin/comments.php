<?php
@include '../components/connect.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
// session_start();

$admin_id = $_SESSION['admin_id'];
if (!isset($admin_id)) {
    header('location:admin_login.php');
}
requireAdminLogin();

$comments = getAllCommentsAdmin();
$pageTitle = 'Manage Comments';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Comments</title>

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/admin-Style.css">


</head>

<body>
    <?php include '../components/admin_header.php'; ?>

    <div class="admin-table-card mt-4 me-3 shadow-lg">
        <div class="admin-table-header">
            <h5><i class="fas fa-comments me-2"></i>All Comments (<?= count($comments) ?>)</h5>
        </div>
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead style="background-color: aliceblue;">
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Comment</th>
                        <th>Post</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($comments)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No comments yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($comments as $c): ?>
                            <tr>
                                <td style="color:var(--text-muted);font-size:0.8rem;"><?= $c['id'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="user-avatar" style="width:28px;height:28px;font-size:0.7rem;"><?= strtoupper(substr($c['user_name'], 0, 1)) ?></div>
                                        <span style="font-size:0.85rem;font-weight:500;"><?= sanitize($c['user_name']) ?></span>
                                    </div>
                                </td>
                                <td style="max-width:280px;">
                                    <p style="margin:0;font-size:0.82rem;color:var(--text-dark);"><?= sanitize(substr($c['comment'], 0, 100)) ?><?= strlen($c['comment']) > 100 ? '...' : '' ?></p>
                                </td>
                                <td>
                                    <a href="read_post.php?id=<?= $c['post_id'] ?>" target="_blank" style="font-size:0.8rem;color:var(--primary);"><?= sanitize(substr($c['post_title'], 0, 35)) ?>...</a>
                                </td>
                                <td style="font-size:0.78rem;white-space:nowrap;"><?= formatDate($c['date']) ?></td>

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