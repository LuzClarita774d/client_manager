<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: clients.php'); exit; }

// Datos del cliente
$stmt = $mysqli->prepare("SELECT * FROM clients WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$client = $stmt->get_result()->fetch_assoc();
if (!$client) { header('Location: clients.php'); exit; }

// Historial de pagos
$stmt = $mysqli->prepare("SELECT id, due_date, amount_due, paid_at, amount_paid FROM payments WHERE client_id = ? ORDER BY due_date DESC");
$stmt->bind_param('i', $id);
$stmt->execute();
$payments = $stmt->get_result();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Historial — <?php echo htmlspecialchars($client['name']); ?></title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body>
  <?php include __DIR__ . '/../includes/header.php'; ?>
  <div class="container">
    <div class="top-actions">
      <div class="left">
        <h1>Historial de <?php echo htmlspecialchars($client['name']); ?></h1>
      </div>
      <div class="right">
        <?php if (is_admin()): ?>
          <a class="btn" href="payment_form.php?client_id=<?php echo (int)$client['id']; ?>">+ Agregar pago</a>
          <a class="btn" href="client_form.php?id=<?php echo (int)$client['id']; ?>">Editar cliente</a>
        <?php endif; ?>
      </div>
    </div>

    <div class="grid cols-3">
      <div class="card">
        <h3>Datos del cliente</h3>
        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($client['name']); ?></p>
        <p><strong>Clave:</strong> <?php echo htmlspecialchars($client['client_code']); ?></p>
        <p><strong>¿Nuevo usuario?</strong> <?php echo $client['is_new_user'] ? 'Sí' : 'No'; ?></p>
        <p><strong>Instalado el:</strong> <?php echo htmlspecialchars($client['installed_at']); ?></p>
        <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($client['phone']); ?></p>
        <p><strong>Tel. referencia:</strong> <?php echo htmlspecialchars($client['ref_phone']); ?></p>
        <p><strong>Dirección:</strong> <?php echo htmlspecialchars($client['address']); ?></p>
      </div>

      <div class="card" style="grid-column: span 2;">
        <h3>Historial de pagos</h3>
        <table>
          <thead>
            <tr>
              <th>Fecha de pago (límite)</th>
              <th>Cuánto paga</th>
              <th>Pagado el</th>
              <th>Monto pagado</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($p = $payments->fetch_assoc()): ?>
              <tr>
                <td><?php echo htmlspecialchars($p['due_date']); ?></td>
                <td><?php echo htmlspecialchars($p['amount_due']); ?></td>
                <td><?php echo htmlspecialchars($p['paid_at'] ?? '—'); ?></td>
                <td><?php echo htmlspecialchars($p['amount_paid'] ?? '—'); ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card">
      <h3>Ubicación (Leaflet / OpenStreetMap)</h3>
      <div id="map" class="map-wrap"></div>
      <form action="../actions/client_save.php" method="post" style="margin-top:12px;">
        <input type="hidden" name="id" value="<?php echo (int)$client['id']; ?>">
        <div class="row">
          <div>
            <label for="latitude">Latitud</label>
            <input type="text" name="latitude" id="latitude" value="<?php echo htmlspecialchars($client['latitude']); ?>" <?php echo is_admin() ? '' : 'readonly'; ?>>
          </div>
          <div>
            <label for="longitude">Longitud</label>
            <input type="text" name="longitude" id="longitude" value="<?php echo htmlspecialchars($client['longitude']); ?>" <?php echo is_admin() ? '' : 'readonly'; ?>>
          </div>
        </div>
        <?php if (is_admin()): ?>
          <div style="margin-top:12px;">
            <button class="btn primary" type="submit" name="action" value="save_location">Guardar ubicación</button>
          </div>
        <?php endif; ?>
      </form>
    </div>
  </div>

  <script>
  const lat = <?= json_encode($client['latitude'] !== null ? (float)$client['latitude'] : 21.1619) ?>;
  const lng = <?= json_encode($client['longitude'] !== null ? (float)$client['longitude'] : -86.8515) ?>;
  const map = L.map('map').setView([lat, lng], 13);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap'
  }).addTo(map);

  let marker = L.marker([lat, lng], {draggable: <?= is_admin() ? 'true' : 'false' ?> }).addTo(map);

  function updateInputs(ll) {
    document.getElementById('latitude').value = ll.lat.toFixed(6);
    document.getElementById('longitude').value = ll.lng.toFixed(6);
  }

  if (<?= is_admin() ? 'true' : 'false' ?>) {
    map.on('click', (e) => {
      marker.setLatLng(e.latlng);
      updateInputs(e.latlng);
    });
    marker.on('moveend', (e) => updateInputs(e.target.getLatLng()));
  }
  </script>
</body>
</html>
