<?php
// ================== CORE INCLUDES ==================
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// ================== USER + DATA ==================
$currentUser = getCurrentUser();
$categories   = getAllCategories();
$flash        = getFlash();

// Base path (useful if project is in subfolder)
$basePath = '';

// Page variables (set from each page like index.php, post.php)
$pageTitle = $pageTitle ?? 'BlogSphere - Share Your Story';
$pageDesc  = $pageDesc ?? 'BlogSphere - A modern blogging platform for content creators and readers.';
?>
<!DOCTYPE html>
<html lang="en">

<!-- ================== HEAD SECTION ================== -->

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= sanitize($pageTitle) ?> - BlogSphere</title>
    <meta name="description" content="<?= sanitize($pageDesc) ?>">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap & Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Main CSS -->
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <nav class="navbar navbar-blogsphere navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="<?= $basePath ?>index.php">Blog<span>Sphere</span></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-3">
                    <li class="nav-item"><a class="nav-link" href="index.php"> <i class="fas fa-home me-1"></i>Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="posts.php"><i class="fas fa-file-alt me-1"></i>All Posts</a></li>
                    <li class="nav-item"><a class="nav-link" href="all_category.php"><i class="fas fa-layer-group me-1"></i>Categories</a></li>

                </ul>
                <form class="d-flex navbar-search me-3" action="<?= $basePath ?>search.php" method="GET">
                    <input class="form-control" type="search" name="q" placeholder="Search posts..." value="<?= isset($_GET['q']) ? sanitize($_GET['q']) : '' ?>">
                    <button class="btn-search" type="submit"><i class="fas fa-search"></i></button>
                </form>
                <div class="d-flex gap-2 align-items-center">
                    <?php if ($currentUser): ?>
                        <div class="dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
                                <span class="user-avatar"><?= strtoupper(substr($currentUser['name'], 0, 1)) ?></span>
                                <span style="color: rgba(255,255,255,0.9); font-size:0.85rem;"><?= sanitize($currentUser['name']) ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?= $basePath ?>profile.php"><i class="fas fa-user-edit me-2"></i>My Profile</a></li>
                                <li><a class="dropdown-item" href="<?= $basePath ?>liked.php"><i class="fas fa-heart me-2"></i>Liked Posts</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-danger" href="user_logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="<?= $basePath ?>login.php" class="btn-navbar-login">Login</a>
                        <a href="<?= $basePath ?>register.php" class="btn-navbar-register">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <?php if ($flash): ?>
        <div class="container mt-3">
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible alert-auto-dismiss fade show">
                <?= sanitize($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

</body>

</html>