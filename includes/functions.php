<?php
require_once __DIR__ . '/../components/connect.php';


function getDB() {
    require __DIR__ . '/../components/connect.php';
    return $conn;
}

function sanitize($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// function hashPassword($password) {
//     return password_hash($password, PASSWORD_DEFAULT);
// }

function verifyPassword($password, $hash ) {
    return password_verify($password, $hash);
}

// function csrfToken() {
//     if (!isset($_SESSION['csrf_token'])) {
//         $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
//     }
//     return $_SESSION['csrf_token'];
// }

// function verifyCsrf() {
//     return isset($_POST['csrf_token']) &&
//            $_POST['csrf_token'] === $_SESSION['csrf_token'];
// }

//search functions

function searchPosts($query, $limit, $offset) {
    $conn = getDB();
    $stmt = $conn->prepare("
        SELECT p.*, a.name,
        (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS like_count,
        (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count
        FROM posts p
        JOIN admin a ON p.admin_id = a.id
        WHERE p.title LIKE :q OR p.content LIKE :q OR p.category LIKE :q
        ORDER BY p.date DESC
        LIMIT :limit OFFSET :offset
    ");

    $search = "%$query%";

    $stmt->bindValue(':q', $search, PDO::PARAM_STR);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function countSearchResults($query) {
    $conn = getDB();
    $stmt = $conn->prepare("
        SELECT COUNT(*) FROM posts
        WHERE title LIKE :q OR content LIKE :q OR category LIKE :q
    ");

    $search = "%$query%";
    $stmt->bindValue(':q', $search, PDO::PARAM_STR);
    $stmt->execute();

    return $stmt->fetchColumn();
}

function excerpt($text, $limit = 100) {
    $text = strip_tags($text); // remove HTML

    if (strlen($text) > $limit) {
        return substr($text, 0, $limit) . '...';
    }

    return $text;
}

function getAllCategories() {
    $conn = getDB();
    $stmt = $conn->query("
        SELECT category, COUNT(*) as post_count
        FROM posts
        GROUP BY category
        ORDER BY category ASC
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPostById($id) {
    $db = getDB();

    $stmt = $db->prepare("
        SELECT p.*, a.name,
        (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS like_count,
        (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count
        FROM posts p
        JOIN admin a ON p.admin_id = a.id
        WHERE p.id = ?
    ");

    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function formatDate($date) {
    return date("d M Y", strtotime($date));
}

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60) return $diff . " sec ago";
    if ($diff < 3600) return floor($diff/60) . " min ago";
    if ($diff < 86400) return floor($diff/3600) . " hrs ago";
    return floor($diff/86400) . " days ago";
}


// 

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

