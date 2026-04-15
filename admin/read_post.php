<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// if (!isset($_SESSION['admin_id'])) {
//     header("Location: admin_login.php");
//     exit;
// }
requireAdminLogin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: posts.php');
    exit;
}

$post = getPostById($id);
if (!$post) {
    header('Location: posts.php');
    exit;
}

$db = getDB();
$comments = $db->prepare("
    SELECT * FROM comments 
    WHERE post_id = ? 
    ORDER BY date DESC
");
$comments->execute([$id]);
$comments = $comments->fetchAll(PDO::FETCH_ASSOC);
$pageTitle = 'Read Post';
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Read Post</title>

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
</head>

<body>

    <?php include '../components/admin_header.php'; ?>

    <!-- ================== Read POST ================== -->
    <div class="read-post-wrap m-auto mt-3">

        <!-- Top action bar -->
        <div class="read-post-topbar align-items-center justify-content-between mb-4 gap-2 flex-wrap" style="display: flex;">
            <a href="view_posts.php" class="back-btn flex align-items-center gap-1 fs-5 fw-bolder text-decoration-none">
                <i class="fas fa-arrow-left"></i> Back to All Posts
            </a>
            <div class="flex gap-3">
                <a href="edit_post.php?id=<?= $post['id'] ?>" class="btn btn-sm" style="background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;padding:0.4rem 1rem;border-radius:8px;font-size:0.82rem;font-weight:600;">
                    <i class="fas fa-edit me-1"></i>Edit Post
                </a>
                <a href="../post.php?id=<?= $post['id'] ?>" target="_blank" class="btn btn-sm" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:0.4rem 1rem;border-radius:8px;font-size:0.82rem;font-weight:600;">
                    <i class="fas fa-external-link-alt me-1"></i>View Live
                </a>
            </div>
        </div>

        <!-- Post card -->
        <div class="read-post-card bg-white overflow-hidden">

            <!-- Hero image -->
            <?php if ($post['image']): ?>
                <img src="<?= '../uploaded_img/' . $post['image'] ?>" alt="<?= sanitize($post['title']) ?>" class="read-post-hero">
            <?php else: ?>
                <div class="read-post-hero-placeholder">
                    <i class="fas fa-image"></i>
                </div>
            <?php endif; ?>

            <!-- Post header -->
            <div class="read-post-header">
                <div style="margin-bottom:0.5rem;">
                    <span class="read-post-category"><?= sanitize($post['category']) ?></span>
                    <span class="read-post-status-badge <?= $post['status'] ?>">
                        <i class="fas fa-circle me-1" style="font-size:0.5rem;"></i><?= ucfirst($post['status']) ?>
                    </span>
                </div>
                <h1 class="read-post-title"><?= sanitize($post['title']) ?></h1>
                <div class="read-post-meta-row">
                    <div class="read-post-meta-item">
                        <i class="fas fa-user"></i>
                        <span>By <strong><?= sanitize($post['name']) ?></strong></span>
                    </div>
                    <div class="read-post-meta-item">
                        <i class="fas fa-calendar-alt"></i>
                        <span><?= formatDate($post['date']) ?></span>
                    </div>
                    <div class="read-post-meta-item">
                        <i class="fas fa-clock"></i>
                        <span><?= timeAgo($post['date']) ?></span>
                    </div>
                    <div class="read-post-meta-item">
                        <i class="fas fa-hashtag"></i>
                        <span>Post ID: <strong><?= $post['id'] ?></strong></span>
                    </div>
                </div>
            </div>

            <!-- Stats bar -->
            <div class="read-post-stats-bar">
                <div class="read-post-stat likes">
                    <i class="fas fa-heart"></i>
                    <span><?= $post['like_count'] ?> <?= $post['like_count'] == 1 ? 'Like' : 'Likes' ?></span>
                </div>
                <div class="read-post-stat comments">
                    <i class="fas fa-comment"></i>
                    <span><?= $post['comment_count'] ?> <?= $post['comment_count'] == 1 ? 'Comment' : 'Comments' ?></span>
                </div>
            </div>

            <!-- Post content -->
            <div class="read-post-body">
                <div class="read-post-content"><?= sanitize($post['content']) ?></div>
            </div>

            <!-- Comments section -->
            <div class="read-post-comments">
                <div class="read-post-comments-title">
                    <i class="fas fa-comments"></i>
                    Comments
                    <span class="count-badge"><?= count($comments) ?></span>
                </div>

                <?php if (empty($comments)): ?>
                    <div class="read-post-no-comments">
                        <i class="fas fa-comment-slash"></i>
                        No comments on this post yet.
                    </div>
                <?php else: ?>
                    <?php foreach ($comments as $c): ?>
                        <div class="read-comment-item">
                            <div class="read-comment-avatar"><?= strtoupper(substr($c['user_name'], 0, 1)) ?></div>
                            <div class="read-comment-body">
                                <div class="read-comment-head">
                                    <span class="read-comment-author"><?= sanitize($c['user_name']) ?></span>
                                    <span class="read-comment-date"><i class="fas fa-clock me-1"></i><?= timeAgo($c['date']) ?></span>
                                </div>
                                <p class="read-comment-text"><?= sanitize($c['comment']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <!-- JS -->
    <script src="../bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/adminScript.js"></script>

</body>

</html>