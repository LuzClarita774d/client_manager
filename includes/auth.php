<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: index.php');
        exit;
    }
}

function current_user_role() {
    return $_SESSION['role'] ?? null;
}

function is_admin() {
    return (current_user_role() === 'admin');
}

function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
?>
