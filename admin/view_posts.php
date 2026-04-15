<?php
@include '../components/connect.php';
session_start();

// ================== AUTH ==================
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// ================== FUNCTIONS ==================
function sanitize($data)
{
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function formatDate($date)
{
    return date("d M Y", strtotime($date));
}

// ================== DELETE ==================
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    $conn->prepare("DELETE FROM comments WHERE post_id=?")->execute([$id]);
    $conn->prepare("DELETE FROM likes WHERE post_id=?")->execute([$id]);
    $conn->prepare("DELETE FROM posts WHERE id=?")->execute([$id]);

    header("Location: view_posts.php");
    exit();
}

// ================== FILTER ==================
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

$query = "
    SELECT p.*,
    (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS like_count,
    (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count
    FROM posts p
    WHERE 1
";

$params = [];

if ($search) {
    $query .= " AND (p.title LIKE ? OR p.name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category) {
    $query .= " AND p.category = ?";
    $params[] = $category;
}

$query .= " ORDER BY p.id DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// categories (same as add post)
$CATEGORIES = ['Technology', 'Lifestyle', 'Travel', 'Health', 'Fashion', 'Food', 'Sports', 'Business', 'Education', 'Entertainment', 'Science', 'Politics', 'Other'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>View Posts</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">

    <style>
        .table-clean th {
            font-size: 0.8rem;
            color: #6b7280;
            text-transform: uppercase;
        }

        .table-clean td {
            font-size: 0.85rem;
            vertical-align: middle;
        }

        .table-clean tr:hover {
            background: #f9fafb;
        }

        .badge-soft {
            background: rgba(245, 158, 11, 0.15);
            color: #92400e;
            font-size: 0.75rem;
        }

        .action-btn {
            padding: 4px 8px;
            font-size: 0.75rem;
            border-radius: 5px;
        }
    </style>

</head>

<body>

    <?php include '../components/admin_header.php'; ?>

    <div class="container mt-4">

        <div class="dash-card p-4">

            <!-- HEADER -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5><i class="fas fa-file-alt me-2"></i>All Posts</h5>

                <a href="add_posts.php" class="btn text-white btn-sm" style="background:var(--primary);">
                    <i class="fas fa-plus"></i> Add Post
                </a>
            </div>

            <!-- FILTER -->
            <form method="GET" class="d-flex flex-wrap gap-2 mb-3">

                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="Search posts..."
                    value="<?= sanitize($search) ?>" style="max-width:220px;">

                <select name="category" class="form-select form-select-sm" style="max-width:160px;">
                    <option value="">All Categories</option>
                    <?php foreach ($CATEGORIES as $cat): ?>
                        <option value="<?= $cat ?>" <?= $category == $cat ? 'selected' : '' ?>>
                            <?= $cat ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button class="btn btn-sm btn-primary">Filter</button>

                <?php if ($search || $category): ?>
                    <a href="view_posts.php" class="btn btn-sm btn-outline-secondary">Clear</a>
                <?php endif; ?>

            </form>

            <!-- TABLE -->
            <div class="table-responsive">
                <table class="table table-clean mb-0">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Author</th>
                            <th>Date</th>
                            <th>Stats</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (empty($posts)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No posts found</td>
                            </tr>
                        <?php else: ?>

                            <?php foreach ($posts as $post): ?>
                                <tr>

                                    <td><?= $post['id'] ?></td>

                                    <td>
                                        <strong><?= sanitize(substr($post['title'], 0, 50)) ?></strong>
                                    </td>

                                    <td>
                                        <span class="badge badge-soft"><?= sanitize($post['category']) ?></span>
                                    </td>

                                    <td><?= sanitize($post['name']) ?></td>

                                    <td><?= formatDate($post['date']) ?></td>

                                    <td>
                                        <i class="fas fa-heart text-danger"></i> <?= $post['like_count'] ?>
                                        &nbsp;
                                        <i class="fas fa-comment text-primary"></i> <?= $post['comment_count'] ?>
                                    </td>

                                    <td>
                                        <span class="badge <?= $post['status'] == 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= ucfirst($post['status']) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <div class="d-flex gap-1">

                                            <a href="edit_post.php?id=<?= $post['id'] ?>"
                                                class="btn action-btn"
                                                style="background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <a href="?delete=<?= $post['id'] ?>"
                                                onclick="return confirm('Delete this post?')"
                                                class="btn action-btn"
                                                style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <a href="read_post.php?id=<?= $post['id'] ?>" class="btn btn-sm" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;font-size:0.75rem;padding:0.25rem 0.6rem;border-radius:4px;" title="Read Post"><i class="fas fa-eye"></i></a>


                                        </div>
                                    </td>

                                </tr>
                            <?php endforeach; ?>

                        <?php endif; ?>
                    </tbody>

                </table>
            </div>

        </div>
    </div>

    <script src="../bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/adminScript.js"></script>

</body>

</html>