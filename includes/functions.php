<?php
function getDB() {
    global $conn;
    return $conn;
}

function sanitize($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// function hashPassword($password) {
//     return password_hash($password, PASSWORD_DEFAULT);
// }

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function csrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf() {
    return isset($_POST['csrf_token']) &&
           $_POST['csrf_token'] === $_SESSION['csrf_token'];
}