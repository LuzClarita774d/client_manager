<?php
require_once __DIR__ . '/../config/db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $stmt = $mysqli->prepare("SELECT id, email, password_hash, role FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
       header('Location: /client_manager/public/dashboard.php');  // al iniciar sesión ok


        exit;
    } else {
        $_SESSION['error'] = 'Credenciales inválidas';
        header('Location: /client_manager/public/index.php');      // credenciales inválidas


        exit;
    }
} else {
   header('Location: /client_manager/public/index.php');      // si entran por GET


    exit;
}
