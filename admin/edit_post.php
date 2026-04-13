<?php
@include '../components/connect.php';
session_start();

$admin_id = $_SESSION['admin_id'];
if (!isset($admin_id)) {
    header('location:admin_login.php');
}

// ================== GET POST ==================
$post_id = $_GET['id'] ?? null;

if (!$post_id) {
    header("Location: view_posts.php");
    exit();
}

// ================== FETCH POST ==================
$select_post = $conn->prepare("SELECT * FROM posts WHERE id = ?");
$select_post->execute([$post_id]);
$post = $select_post->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    echo "<h3 style='text-align:center;margin-top:50px;'>Post not found</h3>";
    exit();
}

// ================== HELPER FUNCTIONS ==================
function sanitize($data)
{
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// ================== CATEGORIES ==================
$CATEGORIES = ['Technology', 'Lifestyle', 'Travel', 'Health', 'Fashion', 'Food', 'Sports', 'Business', 'Education', 'Entertainment', 'Science', 'Politics', 'Other'];

// ================== INIT ==================
$error = '';
$success = '';

// ================== UPDATE POST ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $status = ($_POST['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';

    if (!$title || !$content || !$category) {
        $error = "All fields are required!";
    } else {

        // ================== IMAGE ==================
        $image = $post['image'];

        if (!empty($_FILES['image']['name'])) {

            $img_name = $_FILES['image']['name'];
            $tmp = $_FILES['image']['tmp_name'];
            $size = $_FILES['image']['size'];

            $ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($ext, $allowed)) {
                $error = "Invalid image type!";
            } elseif ($size > 5 * 1024 * 1024) {
                $error = "Image size must be less than 5MB!";
            } else {

                // delete old image
                if ($image && file_exists("../uploaded_img/" . $image)) {
                    unlink("../uploaded_img/" . $image);
                }

                $new_name = time() . '_' . $img_name;
                move_uploaded_file($tmp, "../uploaded_img/" . $new_name);
                $image = $new_name;
            }
        }

        // ================== UPDATE DB ==================
        if (!$error) {

            $update = $conn->prepare("
                UPDATE posts 
                SET title=?, content=?, category=?, image=?, status=? 
                WHERE id=?
            ");

            $update->execute([$title, $content, $category, $image, $status, $post_id]);

            $success = "Post updated successfully!";

            // refresh data
            $select_post->execute([$post_id]);
            $post = $select_post->fetch(PDO::FETCH_ASSOC);
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post</title>

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/admin_Style.css">


</head>

<body>
    <?php include '../components/admin_header.php'; ?>
    <!-- ================== EDIT POST ================== -->
    <div class="container mt-4">
        <div class="card-header  text-white py-2 ps-3 pt-3 rounded-top-4" style="background:var(--primary);">

            <h4 class="mb-3"><i class="fas fa-edit"></i> Edit Post</h4>
        </div>

        <div class="dash-card p-4">



            <!-- ERROR -->
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= sanitize($error) ?></div>
            <?php endif; ?>

            <!-- SUCCESS -->
            <?php if ($success): ?>
                <div class="alert alert-success"><?= sanitize($success) ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">

                <div class="row g-4">

                    <!-- LEFT SIDE -->
                    <div class="col-lg-8">

                        <!-- TITLE -->
                        <div class="mb-3">
                            <label class="form-label">Post Title *</label>
                            <input type="text" name="title" class="form-control"
                                value="<?= sanitize($post['title']) ?>"
                                required>
                        </div>

                        <!-- CONTENT -->
                        <div class="mb-3">
                            <label class="form-label">Post Content *</label>
                            <textarea name="content" rows="10" class="form-control"
                                required><?= sanitize($post['content']) ?></textarea>
                        </div>

                    </div>

                    <!-- RIGHT SIDE -->
                    <div class="col-lg-4">

                        <div class="p-3 rounded-2" style="background:var(--bg-light);border:1px solid var(--border);">

                            <!-- CATEGORY -->
                            <div class="mb-3">
                                <label class="form-label">Category *</label>
                                <select name="category" class="form-select" required>
                                    <?php foreach ($CATEGORIES as $c): ?>
                                        <option value="<?= $c ?>" <?= $post['category'] == $c ? 'selected' : '' ?>>
                                            <?= $c ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- STATUS -->
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active" <?= $post['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="inactive" <?= $post['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>

                            <!-- IMAGE -->
                            <div class="mb-3">
                                <label class="form-label">Image</label>

                                <?php if (!empty($post['image'])): ?>
                                    <img src="../uploaded_img/<?= sanitize($post['image']) ?>"
                                        class="img-fluid rounded mb-2"
                                        style="max-height:120px;object-fit:cover;width:100%;">
                                <?php endif; ?>

                                <input type="file" name="image" class="form-control">
                            </div>

                        </div>

                        <!-- BUTTONS -->
                        <div class="d-grid gap-2 mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update
                            </button>

                            <a href="view_posts.php" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>

                    </div>

                </div>

            </form>

        </div>

    </div>







    <!-- JS -->

    <script src="../bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script src="../js/adminScript.js"></script>


</body>

</html>