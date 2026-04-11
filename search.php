<?php
session_start();

require_once __DIR__ . '/components/connect.php';
require_once __DIR__ . '/includes/functions.php';

// check login safely
if (!isset($_SESSION['admin_id'])) {
    header('location: admin_login.php');
    exit;
}

// get search query
$query = trim($_GET['q'] ?? '');

$perPage = 6;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$posts = [];
$totalPosts = 0;

// run search only if query exists
if (!empty($query)) {
    $posts = searchPosts($query, $perPage, $offset);
    $totalPosts = countSearchResults($query);
}

$totalPages = ($totalPosts > 0) ? ceil($totalPosts / $perPage) : 1;

//  safe category fetch
$categories = function_exists('getAllCategories') ? getAllCategories() : [];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Posts</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/admin-Style.css">
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>
    <!--HERO -->
    <section class="search-hero">

        <div class="container text-center">
            <h1><i class="fas fa-search me-2"></i>Search Posts</h1>

            <form class="search-form" method="GET">
                <div class="input-group">
                    <input type="text" class="form-control"
                        name="q"
                        placeholder="Search posts..."
                        value="<?= sanitize($query) ?>"
                        required>
                    <button class="btn-search-lg">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>

            <?php if ($query): ?>
                <p class="mt-3 text-light">
                    Found <strong><?= $totalPosts ?></strong> result(s) for
                    "<em><?= sanitize($query) ?></em>"
                </p>
            <?php endif; ?>
        </div>
    </section>

    <!-- CONTENT -->
    <div class="container my-5">
        <div class="row g-4">

            <!-- LEFT -->
            <div class="col-lg-8">

                <div class="text-center py-5">


                    <i class="fas fa-search fs-2 mb-2"></i>
                    <h4 class="fw-semibold">Search for Posts</h4>
                    <p class="text-muted">
                        Enter keywords above to find articles you're interested in.
                    </p>
                </div>

            </div>

            <!-- RIGHT SIDEBAR -->
            <div class="col-lg-4">

                <div class="card shadow-sm border-0 rounded-4">

                    <div class="card-body">

                        <h5 class="fw-semibold mb-3">
                            <i class="fas fa-layer-group me-2"></i>Browse Categories
                        </h5>

                        <?php foreach ($categories as $cat): ?>
                            <a href="category.php?cat=<?= urlencode($cat['category']) ?>"
                                class="d-flex justify-content-between align-items-center 
                      bg-light rounded-3 px-3 py-2 mb-2 text-decoration-none text-dark">

                                <span><?= sanitize($cat['category']) ?></span>

                                <span class="badge bg-warning text-dark rounded-pill">
                                    <?= $cat['post_count'] ?>
                                </span>

                            </a>
                        <?php endforeach; ?>

                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- JS -->
    <script src="./bootstrap-5.3.8-dist/bootstrap.bundle.min.js"></script>

</body>

</html>