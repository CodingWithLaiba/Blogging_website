<?php
// ================== DATABASE & SESSION ==================
@include '../components/connect.php';
session_start();

// ================== AUTH CHECK ==================
$admin_id = $_SESSION['admin_id'] ?? null;

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// ================== HELPER FUNCTIONS ==================
function sanitize($data)
{
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// ================== INIT ==================
$error = '';
$success = '';

// Categories (same style as your project)
$CATEGORIES = ['Technology', 'Lifestyle', 'Travel', 'Health', 'Fashion', 'Food', 'Sports', 'Business', 'Education', 'Entertainment', 'Science', 'Politics', 'Other'];

// ================== FORM SUBMIT ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get data
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $status = ($_POST['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';

    // Validation
    if (!$title || !$content || !$category) {
        $error = "All fields are required!";
    } else {

        // ================== IMAGE UPLOAD ==================
        $image = '';

        if (!empty($_FILES['image']['name'])) {
            $image_name = $_FILES['image']['name'];
            $image_tmp = $_FILES['image']['tmp_name'];
            $image_size = $_FILES['image']['size'];

            $ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($ext, $allowed)) {
                $error = "Invalid image type!";
            } elseif ($image_size > 5 * 1024 * 1024) {
                $error = "Image size must be less than 5MB!";
            } else {
                $new_name = time() . '_' . $image_name;
                $upload_path = '../uploaded_img/' . $new_name;

                move_uploaded_file($image_tmp, $upload_path);
                $image = $new_name;
            }
        }

        // ================== INSERT INTO DATABASE ==================
        if (!$error) {
            try {
                $insert = $conn->prepare("
                    INSERT INTO posts 
                    (admin_id, name, title, content, category, image, date, status) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)
                ");

                // Get admin name
                $get_admin = $conn->prepare("SELECT name FROM admin WHERE id = ?");
                $get_admin->execute([$admin_id]);
                $admin = $get_admin->fetch(PDO::FETCH_ASSOC);

                $insert->execute([
                    $admin_id,
                    $admin['name'],
                    $title,
                    $content,
                    $category,
                    $image,
                    $status
                ]);

                $success = "Post added successfully!";
            } catch (PDOException $e) {
                $error = "Database error!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Post</title>

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/admin_Style.css">
</head>

<body>

    <?php include '../components/admin_header.php'; ?>

    <!-- ================== ADD POST FORM ================== -->
    <div class="container mt-4">
        <div class="card-header  text-white py-2 ps-3 pt-3 rounded-top-4" style="background:var(--primary);">

            <h4 class="mb-3 "><i class="fas fa-plus"></i> Add New Post</h4>
        </div>
        <div class="dash-card p-4">

            <!-- Error -->
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= sanitize($error) ?></div>
            <?php endif; ?>

            <!-- Success -->
            <?php if ($success): ?>
                <div class="alert alert-success"><?= sanitize($success) ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">

                <div class="row g-4">

                    <!-- LEFT SIDE -->
                    <div class="col-lg-8">

                        <!-- Title -->
                        <div class="mb-3">
                            <label class="form-label">Post Title *</label>
                            <input type="text" name="title" class="form-control"
                                value="<?= sanitize($_POST['title'] ?? '') ?>"
                                placeholder="Enter title..." required>
                        </div>

                        <!-- Content -->
                        <div class="mb-3">
                            <label class="form-label">Post Content *</label>
                            <textarea name="content" rows="10" class="form-control"
                                placeholder="Write your content..." required><?= sanitize($_POST['content'] ?? '') ?></textarea>
                        </div>

                    </div>

                    <!-- RIGHT SIDE -->
                    <div class="col-lg-4">

                        <div class="p-3 rounded-2" style="background:var(--bg-light);border:1px solid var(--border);">

                            <!-- Category -->
                            <div class="mb-3">
                                <label class="form-label">Category *</label>
                                <select name="category" class="form-select" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($CATEGORIES as $c): ?>
                                        <option value="<?= $c ?>" <?= ($_POST['category'] ?? '') === $c ? 'selected' : '' ?>>
                                            <?= $c ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Status -->
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active">Active</option>
                                    <option value="inactive" <?= ($_POST['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>

                            <!-- Image -->
                            <div class="mb-3">
                                <label class="form-label">Image</label>
                                <input type="file" name="image" class="form-control">
                            </div>

                        </div>

                        <!-- Buttons -->
                        <div class="d-grid gap-2 mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Publish
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
    <script src="../js/adminScript.js"></script>

</body>

</html>