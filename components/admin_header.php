<?php
@include __DIR__ . '/connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get admin data from database
$admin_id = $_SESSION['admin_id'];
$select_admin = $conn->prepare("SELECT * FROM admin WHERE id = ?");
$select_admin->execute([$admin_id]);
$currentAdmin = $select_admin->fetch(PDO::FETCH_ASSOC);

// current page
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- SIDEBAR -->
<aside class="admin-sidebar" id="adminSidebar">

    <a href="dashboard.php" class="sidebar-brand">
        Blog<span class="text-warning">Sphere</span>
        <span class="admin-text">ADMIN PANEL</span>
    </a>

    <nav class="sidebar-menu">

        <div class="menu-label">Main</div>
        <a href="dashboard.php" class="<?= ($current_page == 'dashboard.php') ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>

        <div class="menu-label">Content</div>
        <a href="view_posts.php" class="<?= ($current_page == 'view_posts.php') ? 'active' : '' ?>">
            <i class="fas fa-file-alt"></i> View Posts
        </a>

        <a href="add_posts.php" class="<?= ($current_page == 'add_posts.php') ? 'active' : '' ?>">
            <i class="fas fa-plus-circle"></i> Add New Post
        </a>

        <a href="comments.php" class="<?= ($current_page == 'comments.php') ? 'active' : '' ?>">
            <i class="fas fa-comments"></i> Comments
        </a>

        <div class="menu-label">Users</div>
        <a href="users_accounts.php" class="<?= ($current_page == 'users_accounts.php') ? 'active' : '' ?>">
            <i class="fas fa-users"></i> User Accounts
        </a>

        <div class="menu-label">Admin</div>
        <a href="admin_accounts.php" class="<?= ($current_page == 'admins.php') ? 'active' : '' ?>">
            <i class="fas fa-user-shield"></i> Admin Accounts
        </a>

        <a href="register_admin.php" class="<?= ($current_page == 'register_admin.php') ? 'active' : '' ?>">
            <i class="fas fa-user-plus"></i> Add Admin
        </a>

        <a href="update_profile.php" class="<?= ($current_page == 'update-account.php') ? 'active' : '' ?>">
            <i class="fas fa-user-cog"></i> Update Profile
        </a>

        <div class="menu-label">Website</div>
        <a href="../home.php" target="_blank">
            <i class="fas fa-external-link-alt"></i> View Website
        </a>

        <a href="../components/admin_logout.php" class="text-danger">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>

    </nav>
</aside>


<!-- MAIN AREA -->
<div class="admin-main">

    <!-- TOPBAR -->
    <div class="admin-topbar">
        <div class="d-flex align-items-center justify-content-between gap-3 w-100">


            <div class="d-flex align-items-center gap-3 flex-grow-1 ">
                <!-- Toggle Button (mobile only) -->
                <button class="sidebar-toggle d-lg-none" id="toggleSidebar">
                    <i class="fas fa-bars"></i>
                </button>


                <div class="fw-semibold text-muted small ">
                    <?php
                    $pageNames = [
                        'dashboard.php' => 'Dashboard',
                        'view_posts.php' => 'View Posts',
                        'add_posts.php' => 'Add New Post',
                        'edit_post.php' => 'Edit Post',
                        'comments.php' => 'Comments',
                        'users_accounts.php' => 'User Accounts',
                        'admin_accounts.php' => 'Admin Accounts',
                        'register_admin.php' => 'Register User',
                        'update_profile.php' => 'Update Profile'
                    ];
                    $currentPageName = $pageNames[$current_page] ?? 'Dashboard';
                    echo 'Dashboard / ' . $currentPageName;
                    ?>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 gap-md-3">

                <a href="../home.php" target="_blank" class="btn btn-sm view-site-btn">
                    <i class="fas fa-eye"></i> <span class="d-none d-md-inline">View Site</span>
                </a>

                <div class="d-flex align-items-center gap-2">
                    <div class="user-avatar"><?= strtoupper(substr($currentAdmin['name'], 0, 1)) ?></div>
                    <span class="fw-medium d-none d-md-inline"><?= $currentAdmin['name'] ?></span>
                </div>

            </div>
        </div>
    </div>