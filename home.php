<?php
// ===================== BACKEND SECTION =====================

require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Page Info
$pageTitle = 'Home';
$pageDesc  = 'Discover amazing blog posts on BlogSphere.';

// Pagination Setup
$perPage = 6;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

// Fetch Data from DB
$posts = getAllPosts($perPage, $offset);
$totalPosts = countAllPosts();
$totalPages = ceil($totalPosts / $perPage);

$categories = getAllCategories();
$recentPosts = getRecentPosts(5);
$stats = getAdminStats();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Blogging Website</title>

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <?php include './components/user-header.php'; ?>

    <!-- HERO SECTION -->
<section class="hero-section">
    <div class="container">

        <div class="row align-items-center">

            <!-- LEFT SIDE -->
            <div class="col-lg-7">

                <p class="text-uppercase" style="color:var(--accent);font-weight:600;">
                    Welcome to BlogSphere
                </p>

                <h1>
                    Your Platform for Stories, Ideas & Knowledge
                </h1>

                <p>
                    Discover articles from technology, travel, health and lifestyle.
                </p>

                <a href="#posts" class="btn btn-primary">
                    Explore Posts
                </a>

                <?php if (!isUserLoggedIn()): ?>
                    <a href="register.php" class="btn btn-outline-light">
                        Join Free
                    </a>
                <?php endif; ?>

            </div>

            <!-- RIGHT SIDE -->
            <div class="col-lg-5">

                <!-- STATS -->
                <div class="stats-box">

                    <div>
                        <i class="fas fa-file-alt"></i>
                        <h4><?= $stats['total_posts'] ?></h4>
                        <p>Posts</p>
                    </div>

                    <div>
                        <i class="fas fa-users"></i>
                        <h4><?= $stats['total_users'] ?></h4>
                        <p>Readers</p>
                    </div>

                    <div>
                        <i class="fas fa-comments"></i>
                        <h4><?= $stats['total_comments'] ?></h4>
                        <p>Comments</p>
                    </div>

                </div>

                <!-- LATEST POST -->
                <?php if (!empty($posts)): ?>
                    <?php $latest = $posts[0]; ?>

                    <div class="latest-post-box">
                        <h6>Latest Post</h6>

                        <p>
                            <?= sanitize($latest['title']) ?>
                        </p>

                        <a href="post.php?id=<?= $latest['id'] ?>">
                            Read Now →
                        </a>
                    </div>
                <?php endif; ?>

            </div>

        </div>
    </div>
</section>


<!-- POSTS SECTION -->
<div class="container my-5" id="posts">

    <h2>Latest Posts</h2>
    <p><?= $totalPosts ?> articles found</p>

    <div class="row">

        <?php if (empty($posts)): ?>

            <p>No posts found.</p>

        <?php else: ?>

            <?php foreach ($posts as $post): ?>

                <div class="col-md-6">

                    <div class="post-card">

                        <!-- IMAGE -->
                        <?php if (!empty($post['image'])): ?>
                            <img src="<?= postImageUrl($post['image']) ?>" alt="">
                        <?php else: ?>
                            <div class="no-image">No Image</div>
                        <?php endif; ?>

                        <!-- CONTENT -->
                        <h3>
                            <a href="post.php?id=<?= $post['id'] ?>">
                                <?= sanitize($post['title']) ?>
                            </a>
                        </h3>

                        <p>
                            <?= excerpt($post['content'], 150) ?>
                        </p>

                        <small>
                            By <?= sanitize($post['name']) ?> |
                            <?= formatDate($post['date']) ?>
                        </small>

                        <div class="meta">
                            ❤️ <?= $post['like_count'] ?>
                            💬 <?= $post['comment_count'] ?>
                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>


    <!-- PAGINATION -->
    <?php if ($totalPages > 1): ?>

        <div class="pagination">

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

        </div>

    <?php endif; ?>

</div>


<!-- SIDEBAR SECTION -->
<div class="container">

    <div class="row">

        <div class="col-md-4">

            <h4>Categories</h4>

            <?php foreach ($categories as $cat): ?>
                <a href="category.php?cat=<?= urlencode($cat['category']) ?>">
                    <?= sanitize($cat['category']) ?>
                    (<?= $cat['post_count'] ?>)
                </a><br>
            <?php endforeach; ?>

        </div>

        <div class="col-md-4">

            <h4>Recent Posts</h4>

            <?php foreach ($recentPosts as $rp): ?>
                <a href="post.php?id=<?= $rp['id'] ?>">
                    <?= sanitize($rp['title']) ?>
                </a><br>
            <?php endforeach; ?>

        </div>

        <div class="col-md-4">

            <?php if (!isUserLoggedIn()): ?>
                <div class="join-box">
                    <h4>Join BlogSphere</h4>
                    <p>Create account to comment & like posts</p>
                    <a href="register.php">Get Started</a>
                </div>
            <?php endif; ?>

        </div>

    </div>

</div>

<?php include './components/user-footer.php'; ?>

  <!-- JS -->
    <script src="../bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script src="./js/script.js"></script>
</body>
</html>