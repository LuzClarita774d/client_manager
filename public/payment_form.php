<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
if (!is_admin()) { header('Location: clients.php'); exit; }

$client_id = (int)($_GET['client_id'] ?? 0);
$stmt = $mysqli->prepare("SELECT id, name, installed_at FROM clients WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $client_id);
$stmt->execute();
$client = $stmt->get_result()->fetch_assoc();
if (!$client) { header('Location: clients.php'); exit; }

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

$due_date = '—';
if (!empty($client['installed_at'])) {
  $due_date = due_for_current_month($client['installed_at']); // SIEMPRE mes actual
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Agregar pago</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  <?php include __DIR__ . '/../includes/header.php'; ?>
  <div class="container">
    <div class="card">
      <h1>Agregar pago para <?php echo htmlspecialchars($client['name']); ?></h1>

      <form action="../actions/payment_save.php" method="post">
        <input type="hidden" name="client_id" value="<?php echo (int)$client['id']; ?>">
        <input type="hidden" name="due_date" value="<?php echo htmlspecialchars($due_date); ?>">

        <div class="row">
          <div>
            <label>Fecha límite de pago (automática)</label>
            <input type="text" value="<?php echo htmlspecialchars($due_date); ?>" readonly>
          </div>
          <div>
            <label>Monto a pagar</label>
            <input type="number" step="0.01" name="amount_due" required>
          </div>
          <div>
            <label>Fecha de pago (si ya pagó)</label>
            <input type="date" name="paid_at">
          </div>
          <div>
            <label>Monto pagado</label>
            <input type="number" step="0.01" name="amount_paid">
          </div>
        </div>

        <div style="margin-top:12px;">
          <button class="btn primary" type="submit">Guardar pago</button>
          <a class="btn" href="client_detail.php?id=<?php echo (int)$client['id']; ?>">Cancelar</a>
        </div>
      </form>

      <p class="muted" style="margin-top:10px;">
        *La fecha de pago siempre es el <strong>mismo día</strong> que la fecha de instalación en el <strong>mes actual</strong>.
        Si hoy ya pasó esa fecha, el estado aparecerá como <strong>retraso</strong> hasta que se registre el pago.*
      </p>
    </div>
  </div>
</body>
</html>
