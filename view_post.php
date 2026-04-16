<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Get post ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

// Get post
$post = getPostById($id);

if (!$post || $post['status'] !== 'active') {
    header('Location: index.php');
    exit;
}

// Get user
$currentUser = getCurrentUser();
$db = getDB();

/* ================= LIKE LOGIC ================= */
$userLiked = false;

if ($currentUser) {
    $check = $db->prepare("SELECT id FROM likes WHERE user_id=? AND post_id=?");
    $check->execute([$currentUser['id'], $id]);
    $userLiked = $check->fetch() ? true : false;
}

// Handle like click
if (isset($_GET['like']) && $currentUser) {

    if ($userLiked) {
        $db->prepare("DELETE FROM likes WHERE user_id=? AND post_id=?")
            ->execute([$currentUser['id'], $id]);
    } else {
        $db->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)")
            ->execute([$currentUser['id'], $id]);
    }

    header("Location: view_post.php?id=" . $id);
    exit;
}

/* ================= COMMENT LOGIC ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {

    if (!$currentUser) {
        header("Location: login.php");
        exit;
    }

    $comment = trim($_POST['comment']);

    if (!empty($comment)) {
        $stmt = $db->prepare("
            INSERT INTO comments (post_id, user_id, user_name, comment, date)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $id,
            $currentUser['id'],
            $currentUser['name'],
            $comment
        ]);

        header("Location: view_post.php?id=" . $id);
        exit;
    }
}

/* ================= FETCH DATA ================= */
$comments = getPostComments($id);
$categories = getAllCategories();
$relatedPosts = getPostsByCategory($post['category'], 3);
$recentPosts = getRecentPosts(4);

$pageTitle = $post['title'];
$pageDesc = excerpt($post['content'], 160);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post</title>

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">


</head>

<body>
    <?php include 'components/user-header.php'; ?>

    <div class="breadcrumb-section">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="category.php?cat=<?= urlencode($post['category']) ?>"><?= sanitize($post['category']) ?></a></li>
                    <li class="breadcrumb-item active"><?= sanitize(substr($post['title'], 0, 40)) ?>...</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="container my-4">
        <div class="row g-4">
            <div class="col-lg-8">
                <article class="post-single">
                    <?php if ($post['image']): ?>
                        <img src="<?= 'uploaded_img/' . $post['image'] ?>" alt="<?= sanitize($post['title']) ?>" class="post-single-hero w-100">
                    <?php else: ?>
                        <div class="img-placeholder" style="height:350px;"><i class="fas fa-image"></i></div>
                    <?php endif; ?>

                    <div class="post-single-body">
                        <span class="badge mb-3" style="background:var(--accent);font-size:0.8rem;border-radius:20px;padding:0.35rem 0.9rem;">
                            <i class="fas fa-tag me-1"></i><?= sanitize($post['category']) ?>
                        </span>
                        <h1 class="post-single-title"><?= sanitize($post['title']) ?></h1>

                        <div class="post-meta-bar">
                            <span class="meta-item"><i class="fas fa-user-circle"></i> <strong><?= sanitize($post['name']) ?></strong></span>
                            <span class="meta-item"><i class="fas fa-calendar"></i> <?= formatDate($post['date']) ?></span>
                            <span class="meta-item"><i class="fas fa-heart" style="color:var(--danger);"></i> <?= $post['like_count'] ?> likes</span>
                            <span class="meta-item"><i class="fas fa-comment" style="color:var(--primary);"></i> <?= $post['comment_count'] ?> comments</span>
                        </div>

                        <div class="post-content"><?= sanitize($post['content']) ?></div>

                        <div class="d-flex align-items-center gap-3 mt-4 pt-3 border-top">
                            <a href="?id=<?= $post['id'] ?>&like=1" class="btn-like <?= $userLiked ? 'liked' : '' ?>">
                                <i class="fas fa-heart me-2"></i>
                                <span class="like-text"><?= $userLiked ? 'Liked' : 'Like' ?></span>
                                (<span class="like-count"><?= $post['like_count'] ?></span>)
                            </a>
                            <a href="category.php?cat=<?= urlencode($post['category']) ?>" class="btn btn-sm" style="background:var(--bg-light);border:1px solid var(--border);border-radius:20px;font-size:0.85rem;color:var(--text-dark);">
                                <i class="fas fa-tag me-1"></i><?= sanitize($post['category']) ?>
                            </a>
                        </div>
                    </div>
                </article>

                <div class="comment-section mt-4">
                    <h4 class="comment-section-title"><i class="fas fa-comments me-2"></i>Comments (<?= count($comments) ?>)</h4>

                    <?php if (empty($comments)): ?>
                        <div class="text-center py-3 text-muted">
                            <i class="fas fa-comment-slash d-block mb-2" style="font-size:1.8rem;opacity:0.3;"></i>
                            <p>No comments yet. Be the first to share your thoughts!</p>
                        </div>
                    <?php else: ?>
                        <div class="mb-4">
                            <?php foreach ($comments as $comment): ?>
                                <div class="comment-item">
                                    <span class="comment-author"><i class="fas fa-user-circle me-1"></i><?= sanitize($comment['user_name']) ?></span>
                                    <span class="comment-date"><?= formatDate($comment['date']) ?></span>
                                    <p class="comment-text"><?= sanitize($comment['comment']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div id="commentFeedback"></div>

                    <?php if ($currentUser): ?>
                        <h5 class="mb-3" style="font-size:1.05rem;font-weight:600;">Leave a Comment</h5>
                        <form method="POST">
                            <input type="hidden">
                            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                            <div class="mb-3">
                                <textarea class="form-control" name="comment" rows="4" placeholder="Write your comment here..." required></textarea>
                            </div>
                            <button type="submit" class="btn-primary-custom btn">
                                <i class="fas fa-paper-plane me-2"></i>Post Comment
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <a href="login.php?redirect=post.php?id=<?= $post['id'] ?>">Login</a> or
                            <a href="register.php">Register</a> to leave a comment.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="sidebar-widget">
                    <h5 class="sidebar-widget-title"><i class="fas fa-layer-group me-2"></i>Categories</h5>
                    <?php foreach ($categories as $cat): ?>
                        <a href="category.php?cat=<?= urlencode($cat['category']) ?>" class="category-badge">
                            <span><?= sanitize($cat['category']) ?></span>
                            <span class="count"><?= $cat['post_count'] ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>

                <?php if (count($relatedPosts) > 0): ?>
                    <div class="sidebar-widget">
                        <h5 class="sidebar-widget-title"><i class="fas fa-fire me-2"></i>Related Posts</h5>
                        <?php foreach ($relatedPosts as $relp): ?>
                            <?php if ($relp['id'] == $post['id']) continue; ?>
                            <div class="recent-post-item">
                               <?php if ($relp['image']): ?>
                                <img src="<?= 'uploaded_img/' . $relp['image'] ?>" alt="" class="recent-post-thumb">
                            <?php else: ?>
                                <div class="recent-post-thumb img-placeholder" style="min-height:60px;font-size:1.2rem;border-radius:6px;"></div>
                            <?php endif; ?>
                                <div>
                                    <a href="view_post.php?id=<?= $relp['id'] ?>" class="recent-post-title"><?= sanitize($relp['title']) ?></a>
                                    <div class="recent-post-date"><?= formatDate($relp['date']) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="sidebar-widget">
                    <h5 class="sidebar-widget-title"><i class="fas fa-clock me-2"></i>Recent Posts</h5>
                    <?php foreach ($recentPosts as $rp): ?>
                        <div class="recent-post-item">
                            <?php if ($rp['image']): ?>
                                <img src="<?= 'uploaded_img/' . $rp['image'] ?>" alt="" class="recent-post-thumb">
                            <?php else: ?>
                                <div class="recent-post-thumb img-placeholder" style="min-height:60px;font-size:1.1rem;border-radius:6px;flex-shrink:0;"></div>
                            <?php endif; ?>
                            <div>
                                <a href="view_post.php?id=<?= $rp['id'] ?>" class="recent-post-title"><?= sanitize($rp['title']) ?></a>
                                <div class="recent-post-date"><?= formatDate($rp['date']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'components/user-footer.php'; ?>

    <script src="bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>

</html>