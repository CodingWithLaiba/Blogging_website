<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$perPage = 9;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$db = getDB();

// Get filters
$catFilter = trim($_GET['cat'] ?? '');
$search    = trim($_GET['s'] ?? '');
$sort      = $_GET['sort'] ?? 'newest';

// Allowed sorting
$allowedSorts = ['newest', 'oldest', 'popular'];
$sort = in_array($sort, $allowedSorts) ? $sort : 'newest';

// Build WHERE clause
$where  = "p.status = 'active'";
$params = [];

// Category filter
if (!empty($catFilter)) {
    $where .= " AND p.category = ?";
    $params[] = $catFilter;
}

// Search filter (FIXED - case insensitive)
if (!empty($search)) {
    $where .= " AND (LOWER(p.title) LIKE ? OR LOWER(p.content) LIKE ?)";
    $params[] = "%" . strtolower($search) . "%";
    $params[] = "%" . strtolower($search) . "%";
}

// Sorting
switch ($sort) {
    case 'oldest':
        $orderBy = "p.date ASC";
        break;
    case 'popular':
        $orderBy = "like_count DESC";
        break;
    default:
        $orderBy = "p.date DESC";
}

// COUNT query
$countSql = "SELECT COUNT(*) as cnt FROM posts p WHERE $where";
$countStmt = $db->prepare($countSql);
$countStmt->execute($params);
$totalPosts = (int)$countStmt->fetch()['cnt'];
$totalPages = max(1, ceil($totalPosts / $perPage));

// MAIN QUERY (SIMPLIFIED - NO JOIN BUGS)
$sql = "
SELECT p.*,
    (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS like_count,
    (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count
FROM posts p
WHERE $where
ORDER BY $orderBy
LIMIT $perPage OFFSET $offset
";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$posts = $stmt->fetchAll();

// Other data
$categories  = getAllCategories();
$recentPosts = getRecentPosts(5);

// Page title
$pageTitle = $catFilter
    ? $catFilter . ' Posts'
    : ($search ? 'Search: ' . $search : 'All Posts');

$pageDesc = 'Browse all blog posts.';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Posts</title>

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/Style.css">


</head>

<body>
    <?php include 'components/user-header.php'; ?>

    <!-- Page Hero -->
    <section class="posts-hero-section">
        <div class="container position-relative" style="z-index:1;">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb mb-0" style="background:transparent;padding:0;">
                    <li class="breadcrumb-item"><a href="index.php" style="color:rgba(255,255,255,0.65);">Home</a></li>
                    <li class="breadcrumb-item active" style="color:rgba(255,255,255,0.9);">
                        <?= $catFilter ? sanitize($catFilter) : ($search ? 'Search Results' : 'All Posts') ?>
                    </li>
                </ol>
            </nav>
            <div class="d-flex align-items-end justify-content-between flex-wrap gap-3">
                <div>
                    <h1 class="posts-hero-title">
                        <?php if ($catFilter): ?>
                            <i class="fas fa-tag me-2" style="font-size:0.75em;color:var(--accent);"></i><?= sanitize($catFilter) ?>
                        <?php elseif ($search): ?>
                            <i class="fas fa-search me-2" style="font-size:0.75em;color:var(--accent);"></i>Results for "<?= sanitize($search) ?>"
                        <?php else: ?>
                            All Posts
                        <?php endif; ?>
                    </h1>
                    <p class="posts-hero-sub">
                        <?= $totalPosts ?> <?= $totalPosts === 1 ? 'article' : 'articles' ?> found
                        <?php if ($catFilter): ?> in <strong style="color:var(--accent);"><?= sanitize($catFilter) ?></strong><?php endif; ?>
                    </p>
                </div>
                <a href="categories.php" class="posts-hero-cats-link">
                    <i class="fas fa-layer-group me-1"></i>Browse Categories
                </a>
            </div>
        </div>
    </section>

    <!-- Filter Bar -->
    <div class="posts-filter-bar">
        <div class="container">
            <form method="GET" action="posts.php" class="posts-filter-form">
                <div class="posts-filter-search">
                    <i class="fas fa-search"></i>
                    <input type="text" name="s" placeholder="Search articles…" value="<?= sanitize($search) ?>">
                </div>
                <select name="cat" class="posts-filter-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= sanitize($c['category']) ?>" <?= $c['category'] === $catFilter ? 'selected' : '' ?>>
                            <?= sanitize($c['category']) ?> (<?= $c['post_count'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="sort" class="posts-filter-select">
                    <option value="newest" <?= $sort === 'newest'  ? 'selected' : '' ?>>Newest First</option>
                    <option value="oldest" <?= $sort === 'oldest'  ? 'selected' : '' ?>>Oldest First</option>
                    <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Most Popular</option>
                </select>
                <button type="submit" class="posts-filter-btn"><i class="fas fa-filter me-1"></i>Filter</button>
                <?php if ($catFilter || $search || $sort !== 'newest'): ?>
                    <a href="posts.php" class="posts-filter-clear"><i class="fas fa-times me-1"></i>Clear</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Category Pills -->
    <div class="container mt-4">
        <div class="category-filter">
            <a href="posts.php" class="<?= !$catFilter && !$search ? 'active' : '' ?>">
                <i class="fas fa-th-large me-1"></i>All
            </a>
            <?php foreach ($categories as $c): ?>
                <a href="posts.php?cat=<?= urlencode($c['category']) ?>&sort=<?= $sort ?>"
                    class="<?= $c['category'] === $catFilter ? 'active' : '' ?>">
                    <?= sanitize($c['category']) ?>
                    <span style="opacity:0.65;font-size:0.8em;margin-left:3px;">(<?= $c['post_count'] ?>)</span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Posts Grid + Sidebar -->
    <div class="container pb-5">
        <div class="row g-4">
            <div class="col-lg-8">
                <?php if (empty($posts)): ?>
                    <div class="empty-state">
                        <i class="fas fa-search d-block"></i>
                        <h4>No Posts Found</h4>
                        <p>Try adjusting your filters or search terms.</p>
                        <a href="posts.php" class="btn-primary-custom btn mt-3">View All Posts</a>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($posts as $post): ?>
                            <div class="col-md-6 col-xl-4 fade-in-up">
                                <div class="post-card">
                                    <div class="post-card-img-wrapper">
                                        <?php if ($post['image']): ?>
                                            <img src="<?= 'uploaded_img/' . $post['image'] ?>" alt="<?= sanitize($post['title']) ?>" class="post-card-img">
                                        <?php else: ?>
                                            <div class="img-placeholder" style="height:220px;"><i class="fas fa-image"></i></div>
                                        <?php endif; ?>
                                        <span class="post-card-category"><?= sanitize($post['category']) ?></span>
                                    </div>
                                    <div class="post-card-body">
                                        <h3 class="post-card-title"><a href="post.php?id=<?= $post['id'] ?>"><?= sanitize($post['title']) ?></a></h3>
                                        <p class="post-card-excerpt"><?= excerpt($post['content'], 120) ?></p>
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

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav class="mt-5">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_filter(['cat' => $catFilter, 's' => $search, 'sort' => $sort, 'page' => $page - 1])) ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?<?= http_build_query(array_filter(['cat' => $catFilter, 's' => $search, 'sort' => $sort, 'page' => $i])) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_filter(['cat' => $catFilter, 's' => $search, 'sort' => $sort, 'page' => $page + 1])) ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="sidebar-widget">
                    <h5 class="sidebar-widget-title"><i class="fas fa-layer-group me-2"></i>Categories</h5>
                    <?php foreach ($categories as $cat): ?>
                        <a href="posts.php?cat=<?= urlencode($cat['category']) ?>"
                            class="category-badge <?= $cat['category'] === $catFilter ? 'active' : '' ?>"
                            style="<?= $cat['category'] === $catFilter ? 'background:var(--primary);color:#fff;' : '' ?>">
                            <span><?= sanitize($cat['category']) ?></span>
                            <span class="count"><?= $cat['post_count'] ?></span>
                        </a>
                    <?php endforeach; ?>
                    <a href="categories.php" class="btn-primary-custom btn w-100 mt-3" style="font-size:0.82rem;padding:0.5rem;">
                        <i class="fas fa-th-large me-1"></i>All Categories
                    </a>
                </div>

                <div class="sidebar-widget">
                    <h5 class="sidebar-widget-title"><i class="fas fa-fire me-2"></i>Recent Posts</h5>
                    <?php foreach ($recentPosts as $rp): ?>
                        <div class="recent-post-item">
                            <?php if ($rp['image']): ?>
                                <img src="<?= '/uploaded_img/' . $rp['image'] ?>" alt="" class="recent-post-thumb">
                            <?php else: ?>
                                <div class="recent-post-thumb img-placeholder" style="min-height:60px;font-size:1.2rem;border-radius:6px;"></div>
                            <?php endif; ?>
                            <div>
                                <a href="post.php?id=<?= $rp['id'] ?>" class="recent-post-title"><?= sanitize($rp['title']) ?></a>
                                <div class="recent-post-date"><i class="fas fa-calendar-alt me-1"></i><?= formatDate($rp['date']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (!isUserLoggedIn()): ?>
                    <div class="sidebar-widget text-center" style="background:linear-gradient(135deg,var(--primary-dark),var(--primary));color:#fff;">
                        <i class="fas fa-pen-nib" style="font-size:2rem;color:var(--accent);margin-bottom:0.75rem;display:block;"></i>
                        <h5 style="font-family:'Playfair Display',serif;color:#fff;">Join BlogSphere</h5>
                        <p style="font-size:0.85rem;color:rgba(255,255,255,0.75);">Like posts, leave comments and stay updated!</p>
                        <a href="register.php" class="btn btn-sm mt-2" style="background:var(--accent);color:#fff;border-radius:20px;padding:0.4rem 1.2rem;">Get Started →</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'components/user-footer.php'; ?>


    <script src="bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>

</html>