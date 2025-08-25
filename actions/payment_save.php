<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
if (!is_admin()) { header('Location: /client_manager/public/clients.php'); exit; }

// --- Helpers ---
function last_day_of_month(int $year, int $month): int {
  return (int)date('t', strtotime("$year-$month-01"));
}
function due_for_current_month(string $installedAt): string {
  $now = new DateTime('now');
  $y = (int)$now->format('Y');
  $m = (int)$now->format('m');
  $installDay = (int)date('d', strtotime($installedAt));
  $ldom = last_day_of_month($y, $m);
  $day = min($installDay, $ldom);
  return sprintf('%04d-%02d-%02d', $y, $m, $day);
}

$client_id   = (int)($_POST['client_id'] ?? 0);
$amount_due  = isset($_POST['amount_due']) ? (float)$_POST['amount_due'] : null;
$paid_at     = $_POST['paid_at'] ?? null;
$amount_paid = isset($_POST['amount_paid']) && $_POST['amount_paid'] !== '' ? (float)$_POST['amount_paid'] : null;

if ($client_id <= 0 || $amount_due === null) {
  header('Location: /client_manager/public/clients.php');
  exit;
}

// Obtener fecha de instalación del cliente
$stmt = $mysqli->prepare("SELECT installed_at FROM clients WHERE id=? LIMIT 1");
$stmt->bind_param('i', $client_id);
$stmt->execute();
$client = $stmt->get_result()->fetch_assoc();
if (!$client || !$client['installed_at']) {
  header('Location: /client_manager/public/client_detail.php?id=' . $client_id);
  exit;
}

// Calcular SIEMPRE el vencimiento del MES ACTUAL basado en installed_at
$due_date = due_for_current_month($client['installed_at']);

// Normalizar nulos
if ($paid_at === '') $paid_at = null;
if ($amount_paid === '' || $amount_paid === 0.0) $amount_paid = null;

// ¿Ya existe un pago para este cliente y ese due_date?
$chk = $mysqli->prepare("SELECT id FROM payments WHERE client_id=? AND due_date=? LIMIT 1");
$chk->bind_param('is', $client_id, $due_date);
$chk->execute();
$exist = $chk->get_result()->fetch_assoc();

if ($exist) {
  // Actualiza (por si quieres registrar pago o modificar el monto)
  $upd = $mysqli->prepare("UPDATE payments SET amount_due=?, paid_at=?, amount_paid=? WHERE id=?");
  $id = (int)$exist['id'];
  $upd->bind_param('dssi', $amount_due, $paid_at, $amount_paid, $id);
  $upd->execute();
} else {
  // Inserta nuevo registro para ese vencimiento
  $ins = $mysqli->prepare("INSERT INTO payments (client_id, due_date, amount_due, paid_at, amount_paid) VALUES (?,?,?,?,?)");
  $ins->bind_param('isdss', $client_id, $due_date, $amount_due, $paid_at, $amount_paid);
  $ins->execute();
}

header('Location: /client_manager/public/client_detail.php?id=' . $client_id);
exit;
