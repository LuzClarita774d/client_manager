<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
if (!is_admin()) { header('Location: ../public/clients.php'); exit; }

$action = $_POST['action'] ?? 'save';
$id = (int)($_POST['id'] ?? 0);

if ($action === 'save_location') {
    $lat = $_POST['latitude'] !== '' ? (float)$_POST['latitude'] : null;
    $lng = $_POST['longitude'] !== '' ? (float)$_POST['longitude'] : null;
    $stmt = $mysqli->prepare("UPDATE clients SET latitude = ?, longitude = ? WHERE id = ?");
    if ($lat === null || $lng === null) {
        // Si no hay valores, guarda NULL
        $stmt = $mysqli->prepare("UPDATE clients SET latitude = NULL, longitude = NULL WHERE id = ?");
        $stmt->bind_param('i', $id);
    } else {
        $stmt->bind_param('ddi', $lat, $lng, $id);
    }
    $stmt->execute();
    header('Location: ../public/client_detail.php?id=' . $id);
    exit;
}

$name = trim($_POST['name'] ?? '');
$code = trim($_POST['client_code'] ?? '');
$is_new = isset($_POST['is_new_user']) ? (int)$_POST['is_new_user'] : 1;
$installed_at = $_POST['installed_at'] ?? null;
$phone = trim($_POST['phone'] ?? '');
$ref_phone = trim($_POST['ref_phone'] ?? '');
$address = trim($_POST['address'] ?? '');

if ($id > 0) {
    $stmt = $mysqli->prepare("UPDATE clients SET name=?, client_code=?, is_new_user=?, installed_at=?, phone=?, ref_phone=?, address=? WHERE id=?");
    $stmt->bind_param('ssissssi', $name, $code, $is_new, $installed_at, $phone, $ref_phone, $address, $id);
    $stmt->execute();
} else {
    $stmt = $mysqli->prepare("INSERT INTO clients (name, client_code, is_new_user, installed_at, phone, ref_phone, address) VALUES (?,?,?,?,?,?,?)");
    $stmt->bind_param('ssissss', $name, $code, $is_new, $installed_at, $phone, $ref_phone, $address);
    $stmt->execute();
    $id = $stmt->insert_id;
}

header('Location: ../public/client_detail.php?id=' . $id);
