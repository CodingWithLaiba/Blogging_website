<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Make sure user is logged in
requireUserLogin();

// Get current user
$user = getCurrentUser();
$db = getDB();

// Get liked posts (SIMPLE QUERY)
$stmt = $db->prepare("
    SELECT p.*, 
        (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS like_count
    FROM likes l
    JOIN posts p ON p.id = l.post_id
    WHERE l.user_id = ?
    ORDER BY l.id DESC
");
$stmt->execute([$user['id']]);
$likedPosts = $stmt->fetchAll();

// Other data
$categories = getAllCategories();
$pageTitle = 'Liked Posts';

?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liked Post</title>

    <!-- CSS will be loaded by user-header.php -->

     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">

</head>

<body>
    <?php include 'components/user-header.php'; ?>

    <div class="container my-5">
        <?php if (empty($likedPosts)): ?>
            <div class="empty-state">
                <i class="fas fa-heart-broken"></i>
                <h4>No Liked Posts Yet</h4>
                <p>Start exploring and like the posts you enjoy!</p>
                <a href="posts.php" class="btn-primary-custom btn mt-3"><i class="fas fa-home me-2"></i>Browse Posts</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($likedPosts as $post): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="post-card">
                            <div class="post-card-img-wrapper">
                                <?php if (!empty($post['image'])): ?>
                                    <img src="uploaded_img/" alt="<?= sanitize($post['title']) ?>">
                                <?php else: ?>
                                    <div class="img-placeholder" style="height:220px;"><i class="fas fa-image"></i></div>
                                <?php endif; ?>
                                <span class="post-card-category"><?= sanitize($post['category']) ?></span>
                            </div>
                            <div class="post-card-body">
                                <h3 class="post-card-title"><a href="view_post.php?id=<?= $post['id'] ?>"><?= sanitize($post['title']) ?></a></h3>
                                <p class="post-card-excerpt"><?= excerpt($post['content'], 130) ?></p>
                                <div class="post-card-meta">
                                    <div>
                                        <span class="author"><i class="fas fa-user-circle me-1"></i><?= sanitize($post['name']) ?></span>
                                        <br><small style="color:var(--text-muted);"><?= formatDate($post['date']) ?></small>
                                    </div>
                                    <div class="stats">
                                        <span class="stat-like"><i class="fas fa-heart"></i> <?= $post['like_count'] ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>




    <script src="js/script.js"></script>
</body>

</html>