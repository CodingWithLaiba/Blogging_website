<?php
require_once __DIR__ . '/../components/connect.php';

/* =====================================================
   DATABASE CONNECTION
===================================================== */
function getDB()
{
    require __DIR__ . '/../components/connect.php';
    return $conn;
}

/* =====================================================
   SECURITY HELPERS
===================================================== */
function sanitize($data)
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/* =====================================================
   AUTH HELPERS
===================================================== */
function verifyPassword($password, $hash)
{
    return password_verify($password, $hash);
}

/* =====================================================
   FORMAT HELPERS
===================================================== */
function formatDate($date)
{
    return date("F d, Y", strtotime($date));
}

function timeAgo($datetime)
{
    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60) return $diff . " sec ago";
    if ($diff < 3600) return floor($diff / 60) . " min ago";
    if ($diff < 86400) return floor($diff / 3600) . " hrs ago";
    return floor($diff / 86400) . " days ago";
}

function excerpt($text, $limit = 150)
{
    $text = strip_tags($text);

    if (strlen($text) <= $limit) {
        return $text;
    }

    return substr($text, 0, $limit) . '...';
}

/* =====================================================
   POSTS (HOME PAGE CORE)
===================================================== */

function getAllPosts($limit = 6, $offset = 0)
{
    $db = getDB();

    $stmt = $db->prepare("
        SELECT 
            p.*,
            (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS like_count,
            (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count
        FROM posts p
        WHERE p.status = 'active'
        ORDER BY p.date DESC, p.id DESC
        LIMIT ? OFFSET ?
    ");

    $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(2, (int)$offset, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function countAllPosts()
{
    $db = getDB();

    $stmt = $db->query("SELECT COUNT(*) FROM posts WHERE status = 'active'");
    return (int)$stmt->fetchColumn();
}

function getPostById($id)
{
    $db = getDB();

    $stmt = $db->prepare("
        SELECT 
            p.*,
            (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS like_count,
            (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count
        FROM posts p
        WHERE p.id = ?
    ");

    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/* =====================================================
   SIDEBAR FUNCTIONS
===================================================== */

function getRecentPosts($limit = 5)
{
    $db = getDB();

    $stmt = $db->prepare("
        SELECT id, title, date, image
        FROM posts
        WHERE status = 'active'
        ORDER BY date DESC, id DESC
        LIMIT ?
    ");

    $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllCategories()
{
    $db = getDB();

    $stmt = $db->query("
        SELECT category, COUNT(*) as post_count
        FROM posts
        WHERE status = 'active'
        GROUP BY category
        ORDER BY post_count DESC
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// ger all categogry 
function getPostsByCategory($category, $limit = 3) {
    $db = getDB();

    $stmt = $db->prepare("
        SELECT *
        FROM posts
        WHERE category = ?
          AND status = 'active'
        ORDER BY date DESC
        LIMIT ?
    ");

    $stmt->bindValue(1, $category, PDO::PARAM_STR);
    $stmt->bindValue(2, (int)$limit, PDO::PARAM_INT);

    $stmt->execute();
    return $stmt->fetchAll();
}

function countPostsByCategory(string $category): int {
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM posts WHERE category = ? AND status = 'active'");
    $stmt->execute([$category]);
    return (int)$stmt->fetch()['cnt'];
}
/* =====================================================
   SEARCH FUNCTIONS
===================================================== */

function searchPosts($query, $limit = 10, $offset = 0)
{
    $db = getDB();

    $q = "%$query%";
    
    // Ensure limit and offset are integers
    $limit = (int)$limit;
    $offset = (int)$offset;

    $stmt = $db->prepare("
        SELECT 
            p.*,
            u.name,
            (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS like_count,
            (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count
        FROM posts p
        LEFT JOIN users u ON u.id = p.admin_id
        WHERE (p.title LIKE ? OR p.content LIKE ? OR p.category LIKE ?)
        AND p.status = 'active'
        ORDER BY p.date DESC
        LIMIT $limit OFFSET $offset
    ");

    $stmt->execute([$q, $q, $q]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function countSearchResults($query)
{
    $db = getDB();

    $q = "%$query%";

    $stmt = $db->prepare("
        SELECT COUNT(*)
        FROM posts
        WHERE (title LIKE ? OR content LIKE ? OR category LIKE ?)
        AND status = 'active'
    ");

    $stmt->execute([$q, $q, $q]);

    return (int)$stmt->fetchColumn();
}

/* =====================================================
   COMMENTS
===================================================== */

function getPostComments($postId)
{
    $db = getDB();

    $stmt = $db->prepare("
        SELECT * 
        FROM comments 
        WHERE post_id = ? 
        ORDER BY date DESC
    ");

    $stmt->execute([$postId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* =====================================================
   LIKES
===================================================== */

function hasUserLiked($userId, $postId)
{
    $db = getDB();

    $stmt = $db->prepare("
        SELECT COUNT(*) 
        FROM likes 
        WHERE user_id = ? AND post_id = ?
    ");

    $stmt->execute([$userId, $postId]);

    return (bool)$stmt->fetchColumn();
}

/* =====================================================
   IMAGE HELPER
===================================================== */

function postImageUrl($image)
{
    if (empty($image)) {
        return 'assets/images/default-post.svg';
    }

    return 'uploaded_img/' . $image;
}

/* =====================================================
   ADMIN STATS (HOMEPAGE)
===================================================== */

function getAdminStats()
{
    $db = getDB();

    return [
        'total_posts' => (int)$db->query("SELECT COUNT(*) FROM posts")->fetchColumn(),
        'total_users' => (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        'total_comments' => (int)$db->query("SELECT COUNT(*) FROM comments")->fetchColumn(),
        'total_likes' => (int)$db->query("SELECT COUNT(*) FROM likes")->fetchColumn(),
    ];
}

/* ================== FLASH MESSAGE ================== */
function getFlash()
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']); // show only once
        return $flash;
    }

    return null;
}

// get all comments 
function getAllCommentsAdmin() {
    $db = getDB();

    $stmt = $db->query("
        SELECT 
            comments.id,
            comments.comment,
            comments.date,
            comments.post_id,
            users.name AS user_name,
            posts.title AS post_title
        FROM comments
        JOIN users ON users.id = comments.user_id
        JOIN posts ON posts.id = comments.post_id
        ORDER BY comments.date DESC
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}