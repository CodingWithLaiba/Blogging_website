<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$cat = trim($_GET['cat'] ?? '');
if (!$cat) { header('Location: index.php'); exit; }

$perPage = 6;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;
$posts = getPostsByCategory($cat, $perPage, $offset);
$total = countPostsByCategory($cat);
$totalPages = ceil($total / $perPage) ?: 1;
$categories = getAllCategories();
$pageTitle = $cat . ' - Category';
include 'components/header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="./bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">


</head>

<body>
    <?php include './components/user-header.php'; ?>

    <section style="background:linear-gradient(135deg,var(--primary-dark),var(--primary));padding:3rem 0;color:#fff;">
    <div class="container">
        <div class="d-flex align-items-center gap-3">
            <div class="stat-card-icon blue" style="font-size:1.5rem;"><i class="fas fa-tag"></i></div>
            <div>
                <h1 style="font-size:2rem;margin:0;font-family:'Playfair Display',serif;"><?= sanitize($cat) ?></h1>
                <p style="margin:0;color:rgba(255,255,255,0.7);font-size:0.9rem;"><?= $total ?> post<?= $total !== 1 ? 's' : '' ?> in this category</p>
            </div>
        </div>
    </div>
</section>

<div class="breadcrumb-section">
    <div class="container">
        <nav><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active"><?= sanitize($cat) ?></li>
        </ol></nav>
    </div>
</div>

<div class="container my-5">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="category-filter">
                <a href="index.php">All Posts</a>
                <?php foreach ($categories as $c): ?>
                <a href="category.php?cat=<?= urlencode($c['category']) ?>" class="<?= $c['category'] === $cat ? 'active' : '' ?>">
                    <?= sanitize($c['category']) ?>
                </a>
                <?php endforeach; ?>
            </div>

            <?php if (empty($posts)): ?>
            <div class="empty-state">
                <i class="fas fa-folder-open"></i>
                <h4>No posts in this category yet</h4>
                <a href="index.php" class="btn-primary-custom btn mt-3">Browse All Posts</a>
            </div>
            <?php else: ?>
            <div class="row g-4">
                <?php foreach ($posts as $post): ?>
                <div class="col-md-6">
                    <div class="post-card">
                        <div class="post-card-img-wrapper">
                            <?php if (!empty($post['image'])): ?>
                            <img src="<?= postImageUrl($post['image']) ?>" alt="<?= sanitize($post['title']) ?>">
                            <?php else: ?>
                            <div class="img-placeholder" style="height:220px;"><i class="fas fa-image"></i></div>
                            <?php endif; ?>
                            <span class="post-card-category"><?= sanitize($post['category']) ?></span>
                        </div>
                        <div class="post-card-body">
                            <h3 class="post-card-title"><a href="post.php?id=<?= $post['id'] ?>"><?= sanitize($post['title']) ?></a></h3>
                            <p class="post-card-excerpt"><?= excerpt($post['content'], 140) ?></p>
                            <div class="post-card-meta">
                                <div>
                                    <span class="author"><i class="fas fa-user-circle me-1"></i><?= sanitize($post['name']) ?></span>
                                    <br><small style="color:var(--text-muted);"><?= formatDate($post['date']) ?></small>
                                </div>
                                <div class="stats">
                                    <span class="stat-like"><i class="fas fa-heart"></i> <?= $post['like_count'] ?></span>
                                    <span class="stat-comment"><i class="fas fa-comment"></i> <?= $post['comment_count'] ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?cat=<?= urlencode($cat) ?>&page=<?= $page - 1 ?>"><i class="fas fa-chevron-left"></i></a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?cat=<?= urlencode($cat) ?>&page=<?= $i ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?cat=<?= urlencode($cat) ?>&page=<?= $page + 1 ?>"><i class="fas fa-chevron-right"></i></a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <div class="sidebar-widget">
                <h5 class="sidebar-widget-title"><i class="fas fa-layer-group me-2"></i>All Categories</h5>
                <?php foreach ($categories as $c): ?>
                <a href="category.php?cat=<?= urlencode($c['category']) ?>" class="category-badge <?= $c['category'] === $cat ? 'active' : '' ?>" style="<?= $c['category'] === $cat ? 'background:var(--primary);color:#fff;' : '' ?>">
                    <span><?= sanitize($c['category']) ?></span>
                    <span class="count"><?= $c['post_count'] ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

    <?php include './components/user-footer.php'; ?>

    <!-- JS -->
    <script src="./bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script src="./js/script.js"></script>

</body>

</html>