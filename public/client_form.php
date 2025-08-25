<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
if (!is_admin()) { header('Location: clients.php'); exit; }

$id = (int)($_GET['id'] ?? 0);
$client = [
  'name'=>'','client_code'=>'','installed_at'=>'','is_new_user'=>1,'phone'=>'','ref_phone'=>'','address'=>'','latitude'=>null,'longitude'=>null
];

if ($id > 0) {
  $stmt = $mysqli->prepare("SELECT * FROM clients WHERE id = ? LIMIT 1");
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $client = $stmt->get_result()->fetch_assoc();
  if (!$client) { header('Location: clients.php'); exit; }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo $id>0?'Editar':'Nuevo'; ?> cliente</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  <?php include __DIR__ . '/../includes/header.php'; ?>
  <div class="container">
    <div class="card">
      <h1><?php echo $id>0?'Editar':'Nuevo'; ?> cliente</h1>
      <form action="../actions/client_save.php" method="post">
        <input type="hidden" name="id" value="<?php echo (int)$id; ?>">
        <div class="row">
          <div>
            <label>Nombre del cliente</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($client['name']); ?>" required>
          </div>
          <div>
            <label>Clave del cliente</label>
            <input type="text" name="client_code" value="<?php echo htmlspecialchars($client['client_code']); ?>" required>
          </div>
          <div>
            <label>¿Nuevo usuario?</label>
            <select name="is_new_user">
              <option value="1" <?php echo $client['is_new_user']?'selected':''; ?>>Sí</option>
              <option value="0" <?php echo !$client['is_new_user']?'selected':''; ?>>No</option>
            </select>
          </div>
          <div>
            <label>Fecha de instalación</label>
            <input type="date" name="installed_at" value="<?php echo htmlspecialchars($client['installed_at']); ?>">
          </div>
          <div>
            <label>Teléfono</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($client['phone']); ?>">
          </div>
          <div>
            <label>Teléfono de referencia</label>
            <input type="text" name="ref_phone" value="<?php echo htmlspecialchars($client['ref_phone']); ?>">
          </div>
          <div style="grid-column: span 2;">
            <label>Dirección</label>
            <input type="text" name="address" value="<?php echo htmlspecialchars($client['address']); ?>">
          </div>
        </div>
        <div style="margin-top:12px;">
          <button class="btn primary" type="submit" name="action" value="save">Guardar</button>
          <a class="btn" href="clients.php">Cancelar</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
