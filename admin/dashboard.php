<?php
@include '../components/connect.php';
session_start();

$admin_id = $_SESSION['admin_id'] ?? null;

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}
$select_admin = $conn->prepare("SELECT * FROM admin WHERE id = ?");
$select_admin->execute([$admin_id]);

$admin = $select_admin->fetch(PDO::FETCH_ASSOC);

// TOTAL COUNTS (FAST)
$total_posts = $conn->query("SELECT COUNT(*) FROM posts")->fetchColumn();
$total_users = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_comments = $conn->query("SELECT COUNT(*) FROM comments")->fetchColumn();
$total_likes = $conn->query("SELECT COUNT(*) FROM likes")->fetchColumn();
$total_admins = $conn->query("SELECT COUNT(*) FROM admin")->fetchColumn();

//  RECENT POSTS
$recentPosts = $conn->query("
    SELECT p.*,
    (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS like_count,
    (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count
    FROM posts p
    ORDER BY p.date DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// RECENT COMMENTS 
$recentComments = $conn->query("
    SELECT c.*, p.title AS post_title
    FROM comments c
    JOIN posts p ON c.post_id = p.id
    ORDER BY c.date DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

//  FUNCTIONS
function sanitize($data)
{
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function formatDate($date)
{
    return date("d M Y", strtotime($date));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/admin_Style.css">


</head>

<body>

    <?php include '../components/admin_header.php'; ?>


    <!-- DASHBOARD CONTENT START -->


    <!-- Welcome Section -->
    <div class="dash-welcome flex-column flex-lg-row">
        <div class="dash-welcome-left">
            <div class="dash-avatar">
                <?= strtoupper(substr($admin['name'], 0, 1)) ?>
            </div>
            <div>
                <h4>
                    Good
                    <?= (date('H') < 12) ? 'Morning' : ((date('H') < 17) ? 'Afternoon' : 'Evening') ?>,
                    <?= explode(' ', $admin['name'])[0] ?>!
                </h4>
                <p>
                    Here's your BlogSphere overview for
                    <strong><?= date('l, F j, Y') ?></strong>
                </p>
            </div>
        </div>

        <a href="add_posts.php" class="dash-new-post-btn mt-3 mt-lg-0">
            <i class="fas fa-plus"></i> New Post
        </a>
    </div>

    <!-- Cards -->
    <div class="row mb-3 me-0 g-3 g-lg-2">
        <div class="col-sm-6 col-xl-3">
            <div class="dash-stat-card dash-stat-blue">
                <div class="dash-stat-body">
                    <div class="dash-stat-label">Total Posts</div>
                    <div class="dash-stat-number"><?= $total_posts ?></div>
                    <div class="dash-stat-trend"><i class="fas fa-arrow-up"></i> Published articles</div>
                </div>
                <div class="dash-stat-icon"><i class="fas fa-file-alt"></i></div>
                <div class="dash-stat-wave"></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="dash-stat-card dash-stat-green">
                <div class="dash-stat-body">
                    <div class="dash-stat-label">Registered Users</div>
                    <div class="dash-stat-number"><?= $total_users ?></div>
                    <div class="dash-stat-trend"><i class="fas fa-users"></i> Active members</div>
                </div>
                <div class="dash-stat-icon"><i class="fas fa-users"></i></div>
                <div class="dash-stat-wave"></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="dash-stat-card dash-stat-amber">
                <div class="dash-stat-body">
                    <div class="dash-stat-label">Total Comments</div>
                    <div class="dash-stat-number"><?= $total_comments ?></div>
                    <div class="dash-stat-trend"><i class="fas fa-comments"></i> Discussions</div>
                </div>
                <div class="dash-stat-icon"><i class="fas fa-comments"></i></div>
                <div class="dash-stat-wave"></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="dash-stat-card dash-stat-red">
                <div class="dash-stat-body">
                    <div class="dash-stat-label">Total Likes</div>
                    <div class="dash-stat-number"><?= $total_likes ?></div>
                    <div class="dash-stat-trend"><i class="fas fa-heart"></i> Post reactions</div>
                </div>
                <div class="dash-stat-icon"><i class="fas fa-heart"></i></div>
                <div class="dash-stat-wave"></div>
            </div>
        </div>
    </div>
    <div class="row g-4 me-0">
        <!-- Recent Posts -->
        <div class="col-lg-8">
            <div class="dash-card">
                <div class="dash-card-header">
                    <div class="dash-card-title">
                        <div class="dash-card-title-icon blue"><i class="fas fa-file-alt"></i></div>
                        <div>
                            <h5>Recent Posts</h5>
                            <span>Latest published articles</span>
                        </div>
                    </div>
                    <a href="view_posts.php" class="dash-view-all">View All <i class="fas fa-chevron-right ms-1"></i></a>
                </div>
                <div class="dash-posts-list">
                    <?php if (empty($recentPosts)): ?>
                        <div class="dash-empty"><i class="fas fa-file-alt"></i>
                            <p>No posts yet. <a href="add_posts.php">Create your first post</a></p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentPosts as $i => $post): ?>
                            <div class="dash-post-row">
                                <div class="dash-post-num"><?= $i + 1 ?></div>
                                <div class="dash-post-info">
                                    <a href="view_posts.php" target="_blank" class="dash-post-title"><?= sanitize(substr($post['title'], 0, 50)) ?><?= strlen($post['title']) > 50 ? '…' : '' ?></a>
                                    <div class="dash-post-meta">
                                        <span><i class="fas fa-user"></i> <?= sanitize($post['name']) ?></span>
                                        <span><i class="fas fa-calendar"></i> <?= formatDate($post['date']) ?></span>
                                        <span class="dash-cat-badge"><?= sanitize($post['category']) ?></span>
                                    </div>
                                </div>
                                <div class="dash-post-stats">
                                    <span class="dash-stat-pill red"><i class="fas fa-heart"></i> <?= $post['like_count'] ?></span>
                                    <span class="dash-stat-pill blue"><i class="fas fa-comment"></i> <?= $post['comment_count'] ?></span>
                                </div>
                                <div class="dash-post-actions">
                                    <a href="edit_post.php?id=<?= $post['id'] ?>" class="dash-action-btn edit" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="view_posts.php?delete=<?= $post['id'] ?>"
                                        class="dash-action-btn del"
                                        onclick="return confirm('Delete this post?')" class="dash-action-btn del btn-delete-confirm" data-name="this post" title="Delete"><i class="fas fa-trash"></i></a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>



        <!-- Right column -->

        <div class="col-lg-4">
            <div class="dash-card p-3">
                <h5 class="mb-3">Overview</h5>
                <canvas id="myChart"></canvas>
                <!-- <h3 id="totalPosts"><?= $total_posts ?></h3>
                <h3 id="totalUsers"><?= $total_users ?></h3>
                <?= $total_admins ?>
                <h3 id="totalComments"><?= $total_comments ?></h3>
                <h3 id="totalLikes"><?= $total_likes ?></h3> -->

            </div>
            <div class=" d-flex flex-column gap-4">
                <!-- Recent comments -->

                <div class="dash-card flex-fill">
                    <div class="dash-card-header">
                        <div class="dash-card-title">
                            <div class="dash-card-title-icon green"><i class="fas fa-comments"></i></div>
                            <div>
                                <h5>Recent Comments</h5>
                                <span>Latest activity</span>
                            </div>
                        </div>
                        <a href="comments.php" class="dash-view-all">View All <i class="fas fa-chevron-right ms-1"></i></a>
                    </div>
                    <div class="dash-comments-list">
                        <?php if (empty($recentComments)): ?>
                            <div class="dash-empty"><i class="fas fa-comments"></i>
                                <p>No comments yet.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recentComments as $c): ?>
                                <div class="dash-comment-item">
                                    <div class="dash-comment-avatar"><?= strtoupper(substr($c['user_name'], 0, 1)) ?></div>
                                    <div class="dash-comment-body">
                                        <div class="dash-comment-meta">
                                            <strong><?= sanitize($c['user_name']) ?></strong>
                                            <span><?= formatDate($c['date']) ?></span>
                                        </div>
                                        <p class="dash-comment-text"><?= sanitize(substr($c['comment'], 0, 70)) ?><?= strlen($c['comment']) > 70 ? '…' : '' ?></p>
                                        <div class="dash-comment-post">on: <a href="../post.php?id=<?= $c['post_id'] ?>" target="_blank"><?= sanitize(substr($c['post_title'], 0, 32)) ?>…</a></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>


    </div>

    <!-- JS -->

    <script src="../bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const chartData = {
            totalPosts: <?= $total_posts ?>,
            totalUsers: <?= $total_users ?>,
            totalComments: <?= $total_comments ?>,
            totalLikes: <?= $total_likes ?>,
            totalAdmins: <?= $total_admins ?>,
        };
    </script>
    <script src="../js/adminScript.js"></script>
    <!-- <script>
    function checkScriptConnection() {
  console.log(" External JS file is connected!");
}
checkScriptConnection();
</script> -->

</body>

</html>