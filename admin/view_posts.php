<?php
@include '../components/connect.php';
session_start();

// ================== AUTH CHECK ==================
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// ================== CATEGORIES (FIXED) ==================
$CATEGORIES = [
    'Technology','Lifestyle','Travel','Health','Fashion',
    'Food','Sports','Business','Education','Entertainment',
    'Science','Politics','Other'
];

// ================== DELETE POST ==================
if (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];

    // delete image
    $get_img = $conn->prepare("SELECT image FROM posts WHERE id = ?");
    $get_img->execute([$delete_id]);
    $img = $get_img->fetchColumn();

    if ($img && file_exists("../uploaded_img/$img")) {
        unlink("../uploaded_img/$img");
    }

    // delete post
    $delete = $conn->prepare("DELETE FROM posts WHERE id = ?");
    $delete->execute([$delete_id]);

    header("Location: view_posts.php");
    exit();
}

// ================== FILTERS ==================
$search = $_GET['s'] ?? '';
$category = $_GET['cat'] ?? '';

$sql = "SELECT * FROM posts WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (title LIKE ? OR name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category) {
    $sql .= " AND category = ?";
    $params[] = $category;
}

$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ================== HELPERS ==================
function sanitize($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function formatDate($date) {
    return date("d M Y", strtotime($date));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Posts</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/adminStyle.css">
</head>

<body>

<?php include '../components/admin_header.php'; ?>

<div class="container mt-4">

    <div class="dash-card p-4">

        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1"><i class="fas fa-file-alt"></i> All Posts</h4>
                <small class="text-muted"><?= count($posts) ?> total posts</small>
            </div>

            <a href="add_posts.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Post
            </a>
        </div>

        <!-- FILTER BAR -->
        <div class="mb-4 p-3 rounded-2" style="background:var(--bg-light);border:1px solid var(--border);">

            <form method="GET" class="row g-2 align-items-center">

                <div class="col-md-4">
                    <input type="text" name="s" class="form-control"
                        placeholder="Search by title or author..."
                        value="<?= sanitize($search) ?>">
                </div>

                <div class="col-md-3">
                    <select name="cat" class="form-select">
                        <option value="">All Categories</option>
                        <?php foreach ($CATEGORIES as $c): ?>
                            <option value="<?= $c ?>" <?= $c == $category ? 'selected' : '' ?>>
                                <?= $c ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-auto">
                    <button class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>

                <div class="col-md-auto">
                    <a href="view_posts.php" class="btn btn-secondary">
                        Clear
                    </a>
                </div>

            </form>

        </div>

        <!-- POSTS LIST -->
        <?php if (empty($posts)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-file-alt fa-2x mb-2"></i>
                <p>No posts found</p>
            </div>
        <?php else: ?>

            <?php foreach ($posts as $post): ?>

                <div class="border-bottom py-3 d-flex justify-content-between align-items-center">

                    <!-- LEFT CONTENT -->
                    <div style="max-width:75%;">

                        <h5 class="mb-1"><?= sanitize($post['title']) ?></h5>

                        <div class="text-muted small">
                            <span><i class="fas fa-user"></i> <?= sanitize($post['name']) ?></span> |
                            <span><i class="fas fa-calendar"></i> <?= formatDate($post['date']) ?></span> |
                            <span class="badge bg-warning text-dark"><?= sanitize($post['category']) ?></span>
                        </div>

                        <div class="mt-1">
                            <span class="<?= $post['status'] === 'active' ? 'badge bg-success' : 'badge bg-secondary' ?>">
                                <?= ucfirst($post['status']) ?>
                            </span>
                        </div>

                    </div>

                    <!-- RIGHT ACTIONS -->
                    <div class="d-flex gap-2">

                        <a href="edit_post.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i>
                        </a>

                        <!-- DELETE FORM -->
                        <form method="POST" onsubmit="return confirm('Delete this post?')">
                            <input type="hidden" name="delete_id" value="<?= $post['id'] ?>">
                            <button class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>

</div>

<script src="../bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/adminScript.js"></script>

</body>
</html>