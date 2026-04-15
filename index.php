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
    <link rel="stylesheet" href="bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include './components/user-header.php'; ?>

    <section class="hero-section">
        <div class="container position-relative" style="z-index:1;">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <p class="mb-2" style="color:var(--accent);font-weight:600;font-size:0.9rem;letter-spacing:1px;text-transform:uppercase;"><i class="fas fa-pen-nib me-2"></i>Welcome to BlogSphere</p>
                    <h1 class="hero-title">Your Platform for <span>Stories</span>, Ideas & Knowledge</h1>
                    <p class="hero-subtitle mt-3 mb-4">Discover insightful articles written by passionate writers. Explore topics from technology to travel, health to lifestyle.</p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="#posts" class="btn-accent ">Explore Posts <i class="fas fa-arrow-down ms-2"></i></a>
                        <?php if (!isUserLoggedIn()): ?>
                            <a href="register.php" class="btn btn-outline-light" style="border-radius:25px;padding:0.65rem 1.5rem;">Join Free <i class="fas fa-user-plus ms-2"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-5 d-none d-lg-block">
                    <div class="d-flex gap-3 mt-4 mt-lg-0">
                        <?php
                        $stats = getAdminStats();
                        $statItems = [
                            ['Posts', $stats['total_posts'], 'fas fa-file-alt'],
                            ['Readers', $stats['total_users'], 'fas fa-users'],
                            ['Comments', $stats['total_comments'], 'fas fa-comments'],
                        ];
                        foreach ($statItems as $s): ?>
                            <div class="hero-stat flex-fill">
                                <i class="<?= $s[2] ?>" style="color:var(--accent);margin-bottom:0.4rem;display:block;font-size:1.3rem;"></i>
                                <span class="stat-number"><?= $s[1] ?>+</span>
                                <span class="stat-label"><?= $s[0] ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (!empty($posts)): $fp = $posts[0]; ?>
                        <div class="mt-3 p-3 rounded-3" style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.12);">
                            <p style="font-size:0.72rem;color:var(--accent);text-transform:uppercase;letter-spacing:1px;margin-bottom:0.3rem;"><i class="fas fa-fire me-1"></i>Latest Post</p>
                            <p style="color:#fff;font-size:0.9rem;font-weight:600;margin:0;"><?= sanitize($fp['title']) ?></p>
                            <a href="view_post.php?id=<?= $fp['id'] ?>" style="color:rgba(255,255,255,0.6);font-size:0.8rem;">Read now →</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <div class="container my-5" id="posts">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h2 class="section-title">Latest Posts</h2>
                        <div class="section-divider"></div>
                    </div>
                    <span style="font-size:0.85rem;color:var(--text-muted);"><?= $totalPosts ?> articles found</span>
                </div>

                <?php if (empty($posts)): ?>
                    <div class="empty-state">
                        <i class="fas fa-file-alt d-block"></i>
                        <h4>No Posts Yet</h4>
                        <p>Be the first to read when new posts are published.</p>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($posts as $post): ?>
                            <div class="col-md-6 fade-in-up">
                                <div class="post-card">
                                    <div class="post-card-img-wrapper">
                                        <?php if (!empty($post['image'])): ?>
                                            <img src="<?= postImageUrl($post['image']) ?>" alt="<?= sanitize($post['title']) ?>">
                                        <?php else: ?>
                                            <div class="post-card-img-wrapper img-placeholder"><i class="fas fa-image"></i></div>
                                        <?php endif; ?>
                                        <span class="post-card-category"><?= sanitize($post['category']) ?></span>
                                    </div>
                                    <div class="post-card-body">
                                        <h3 class="post-card-title"><a href="view_post.php?id=<?= $post['id'] ?>"><?= sanitize($post['title']) ?></a></h3>
                                        <p class="post-card-excerpt"><?= excerpt($post['content'], 150) ?></p>
                                        <div class="post-card-meta">
                                            <div>
                                                <span class="author"><i class="fas fa-user-circle me-1"></i><?= sanitize($post['name']) ?></span>
                                                <br>
                                                <small style="color:var(--text-muted);"><?= formatDate($post['date']) ?></small>
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
                                    <a class="page-link" href="?page=<?= $page - 1 ?>"><i class="fas fa-chevron-left"></i></a>
                                </li>
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $page + 1 ?>"><i class="fas fa-chevron-right"></i></a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <div class="sidebar-widget">
                    <h5 class="sidebar-widget-title"><i class="fas fa-layer-group me-2"></i>Categories</h5>
                    <?php if (empty($categories)): ?>
                        <p class="text-muted small">No categories yet.</p>
                    <?php else: ?>
                        <?php foreach ($categories as $cat): ?>
                            <a href="category.php?cat=<?= urlencode($cat['category']) ?>" class="category-badge">
                                <span><?= sanitize($cat['category']) ?></span>
                                <span class="count"><?= $cat['post_count'] ?></span>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="sidebar-widget">
                    <h5 class="sidebar-widget-title"><i class="fas fa-clock me-2"></i>Recent Posts</h5>
                    <?php foreach ($recentPosts as $rp): ?>
                        <div class="recent-post-item">
                            <?php if (!empty($rp['image'])): ?>
                                <img src="<?= postImageUrl($rp['image']) ?>" alt="" class="recent-post-thumb">
                            <?php else: ?>
                                <div class="recent-post-thumb img-placeholder" style="min-height:60px;font-size:1.2rem;border-radius:6px;"></div>
                            <?php endif; ?>
                            <div>
                                <a href="view_post.php?id=<?= $rp['id'] ?>" class="recent-post-title"><?= sanitize($rp['title']) ?></a>
                                <div class="recent-post-date"><i class="fas fa-calendar-alt me-1"></i><?= formatDate($rp['date']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (!isUserLoggedIn()): ?>
                    <div class="sidebar-widget text-center" style="background:linear-gradient(135deg,var(--primary-dark),var(--primary));color:#fff;">
                        <i class="fas fa-pen-nib" style="font-size:2rem;color:var(--accent);margin-bottom:0.75rem;display:block;"></i>
                        <h5 style="font-family:'Playfair Display',serif;color:#fff;">Join BlogSphere</h5>
                        <p style="font-size:0.85rem;color:rgba(255,255,255,0.75);">Create a free account to like posts, leave comments and more!</p>
                        <a href="register.php" class="btn btn-sm mt-2" style="background:var(--accent);color:#fff;border-radius:20px;padding:0.4rem 1.2rem;">Get Started →</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>


    <?php include './components/user-footer.php'; ?>

    <!-- JS -->
    <script src="bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.js"></script>
    <script src="js/script.js"></script>
</body>

</html>