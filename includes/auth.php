<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireAdminLogin() {
    if (!isset($_SESSION['admin_id'])) {
        header('Location: admin_login.php');
        exit;
    }
}

function getCurrentAdmin() {
    return [
        'id' => $_SESSION['admin_id'],
        'name' => $_SESSION['admin_name']
    ];
}

function isUserLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_name']);
}