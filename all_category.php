<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$db = getDB();

// Get all categories with post count + latest 3 posts per category
$cats = $db->query("SELECT category, COUNT(*) as post_count FROM posts WHERE status='active' GROUP BY category ORDER BY post_count DESC")->fetchAll();

// For each category, get up to 3 latest posts and top post image
$catData = [];
foreach ($cats as $c) {
    $stmt = $db->prepare("SELECT id, title, name, date, image, content FROM posts WHERE category=? AND status='active' ORDER BY date DESC, id DESC LIMIT 3");
    $stmt->execute([$c['category']]);
    $catPosts = $stmt->fetchAll();

    // Find a cover image (first post with an image)
    $cover = '';
    foreach ($catPosts as $p) {
        if (!empty($p['image'])) { $cover = $p['image']; break; }
    }

    $catData[] = [
        'name'       => $c['category'],
        'post_count' => $c['post_count'],
        'cover'      => $cover,
        'posts'      => $catPosts,
    ];
}

$totalPosts = array_sum(array_column($cats, 'post_count'));

$pageTitle = 'All Categories';
$pageDesc  = 'Browse all categories on BlogSphere — Technology, Lifestyle, Travel, Health and more.';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css\style.css">
</head>
<body>

<?php include 'components/user-header.php'; ?>
    <!-- Page Hero -->
<section class="cats-hero">
    <div class="container position-relative" style="z-index:1;">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0" style="background:transparent;padding:0;">
                <li class="breadcrumb-item"><a href="index.php" style="color:rgba(255,255,255,0.65);">Home</a></li>
                <li class="breadcrumb-item active" style="color:rgba(255,255,255,0.9);">All Categories</li>
            </ol>
        </nav>
        <h1 class="cats-hero-title">Explore by Category</h1>
        <p class="cats-hero-sub">
            Dive into <strong style="color:var(--accent);"><?= count($cats) ?> categories</strong>
            spanning <strong style="color:var(--accent);"><?= $totalPosts ?> articles</strong> — find your perfect read.
        </p>
        <a href="posts.php" class="cats-hero-link">
            <i class="fas fa-th-list me-1"></i>View All Posts
        </a>
    </div>
</section>

<!-- Category Stats Strip -->
<div class="cats-strip">
    <div class="container">
        <div class="cats-strip-inner">
            <?php foreach ($catData as $cd): ?>
            <a href="posts.php?cat=<?= urlencode($cd['name']) ?>" class="cats-strip-pill">
                <span class="cats-strip-name"><?= sanitize($cd['name']) ?></span>
                <span class="cats-strip-count"><?= $cd['post_count'] ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Categories Grid -->
<div class="container py-5">
    <?php if (empty($catData)): ?>
    <div class="empty-state">
        <i class="fas fa-layer-group d-block"></i>
        <h4>No Categories Yet</h4>
        <p>Posts will appear here once they are published.</p>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <?php foreach ($catData as $i => $cd):
            $colors = [
                ['#1d4ed8','#3b82f6'],['#059669','#10b981'],
                ['#d97706','#f59e0b'],['#7c3aed','#8b5cf6'],
                ['#dc2626','#ef4444'],['#0e7490','#06b6d4'],
                ['#be185d','#ec4899'],['#ca8a04','#eab308'],
            ];
            [$c1,$c2] = $colors[$i % count($colors)];
        ?>
        <div class="col-md-6 col-xl-4">
            <div class="cat-card">
                <!-- Card header with gradient + icon -->
                <div class="cat-card-header" style="background:linear-gradient(135deg,<?= $c1 ?>,<?= $c2 ?>);">
                    <?php if ($cd['cover']): ?>
                    <img src="<?= postImageUrl($cd['cover']) ?>" alt="<?= sanitize($cd['name']) ?>" class="cat-card-bg-img">
                    <div class="cat-card-overlay"></div>
                    <?php endif; ?>
                    <div class="cat-card-header-inner">
                        <div class="cat-card-icon"><i class="fas fa-tag"></i></div>
                        <div class="cat-card-count"><?= $cd['post_count'] ?> Post<?= $cd['post_count']!=1?'s':'' ?></div>
                        <h3 class="cat-card-name"><?= sanitize($cd['name']) ?></h3>
                    </div>
                </div>

                <!-- Posts preview list -->
                <div class="cat-card-body">
                    <?php if (empty($cd['posts'])): ?>
                    <p style="color:var(--text-muted);font-size:0.85rem;padding:0.5rem 0;">No posts yet.</p>
                    <?php else: ?>
                    <ul class="cat-post-list">
                        <?php foreach ($cd['posts'] as $p): ?>
                        <li class="cat-post-item">
                            <i class="fas fa-chevron-right cat-post-arrow"></i>
                            <div class="cat-post-info">
                                <a href="post.php?id=<?= $p['id'] ?>" class="cat-post-title"><?= sanitize($p['title']) ?></a>
                                <div class="cat-post-date">
                                    <i class="fas fa-user me-1"></i><?= sanitize($p['name']) ?>
                                    <span class="mx-1">·</span>
                                    <i class="fas fa-calendar me-1"></i><?= date('M d, Y', strtotime($p['date'])) ?>
                                </div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>

                    <a href="posts.php?cat=<?= urlencode($cd['name']) ?>" class="cat-card-link"
                       style="background:linear-gradient(135deg,<?= $c1 ?>,<?= $c2 ?>);">
                        Browse <?= sanitize($cd['name']) ?> Posts
                        <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- CTA section -->
    <div class="cats-cta">
        <div class="cats-cta-inner">
            <h3>Can't find what you're looking for?</h3>
            <p>Search across all articles or browse our full post archive.</p>
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="search.php" class="btn-accent btn"><i class="fas fa-search me-2"></i>Search Articles</a>
                <a href="posts.php" class="btn btn-outline-light" style="border-radius:25px;padding:0.65rem 1.5rem;">
                    <i class="fas fa-th-list me-2"></i>All Posts
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'components/user-footer.php'; ?>

<!-- JS -->
    <script src="bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>