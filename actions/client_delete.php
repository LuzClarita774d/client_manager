<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
if (!is_admin()) { header('Location: ../public/clients.php'); exit; }

$id = (int)($_POST['id'] ?? 0);
if ($id > 0) {
    // Elimina pagos del cliente primero (FK simple)
    $stmt = $mysqli->prepare("DELETE FROM payments WHERE client_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();

    $stmt = $mysqli->prepare("DELETE FROM clients WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
}
header('Location: ../public/clients.php');
