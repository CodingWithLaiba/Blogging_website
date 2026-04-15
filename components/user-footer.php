<?php
// ================== DATA INIT ==================
// If categories are already available, use them
// otherwise fetch from database
$categories = $categories ?? getAllCategories();

// Base path for assets (safe fallback)
$basePath = $basePath ?? '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap & Icons -->
    <link rel="stylesheet" href="bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Main CSS -->
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <!-- ================== FOOTER START ================== -->
    <footer class="site-footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="footer-brand">Blog<span>Sphere</span></div>
                    <p class="footer-desc">A modern platform for content creators and readers. Share your stories, ideas, and knowledge with the world.</p>
                    <div class="footer-social mt-3">
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-6">
                    <h6 class="footer-heading">Navigate</h6>
                    <ul class="footer-links list-unstyled">
                        <li><a href="index.php"><i class="fas fa-chevron-right me-1" style="font-size:0.7rem;"></i>Home</a></li>
                        <li><a href="search.php"><i class="fas fa-chevron-right me-1" style="font-size:0.7rem;"></i>Search</a></li>
                        <li><a href="register.php"><i class="fas fa-chevron-right me-1" style="font-size:0.7rem;"></i>Register</a></li>
                        <li><a href="login.php"><i class="fas fa-chevron-right me-1" style="font-size:0.7rem;"></i>Login</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-6">
                    <h6 class="footer-heading">Categories</h6>
                    <ul class="footer-links list-unstyled">
                        <?php foreach (array_slice($categories, 0, 6) as $cat): ?>
                            <li><a href="category.php?cat=<?= urlencode($cat['category']) ?>"><i class="fas fa-chevron-right me-1" style="font-size:0.7rem;"></i><?= sanitize($cat['category']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h6 class="footer-heading">Stay Connected</h6>
                    <p style="font-size:0.85rem; color:rgba(255,255,255,0.6);">Join our community and never miss a new post. Register for free today.</p>
                    <a href="register.php" class="btn btn-sm mt-2" style="background:var(--accent);color:#fff;border-radius:20px;padding:0.4rem 1.2rem;font-size:0.85rem;">
                        <i class="fas fa-pen me-1"></i> Get Started
                    </a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> BlogSphere. All rights reserved. Built with <i class="fas fa-heart" style="color:var(--accent);"></i> for writers and readers.</p>
            </div>
        </div>
    </footer>



    <!-- ================== SCRIPTS ================== -->
    <script src="bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>

</html>