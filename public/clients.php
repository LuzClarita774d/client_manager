<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$q = trim($_GET['q'] ?? '');
$status = trim($_GET['status'] ?? '');

/*
- due_current: día de vencimiento del MES ACTUAL basado en installed_at.
- pcur: pago para ese due_current (si existe).
- status:
  PAID: paid_at <= due_current
  ONTIME: sin pagar y hoy <= due_current
  LATE: sin pagar y hoy > due_current
*/
$sql = "
  SELECT
    t.id,
    t.name,
    t.client_code,
    DATE_FORMAT(t.due_current, '%Y-%m-%d') AS due_current_fmt,
    t.amount_due_current,
    t.status
  FROM (
    SELECT
      c.id,
      c.name,
      c.client_code,
      STR_TO_DATE(
        CONCAT(
          YEAR(CURDATE()), '-',
          LPAD(MONTH(CURDATE()), 2, '0'), '-',
          LPAD(LEAST(DAY(c.installed_at), DAY(LAST_DAY(CURDATE()))), 2, '0')
        ),
        '%Y-%m-%d'
      ) AS due_current,
      pcur.amount_due AS amount_due_current,
      CASE
        WHEN pcur.paid_at IS NOT NULL
             AND DATE(pcur.paid_at) <= STR_TO_DATE(
               CONCAT(
                 YEAR(CURDATE()), '-',
                 LPAD(MONTH(CURDATE()), 2, '0'), '-',
                 LPAD(LEAST(DAY(c.installed_at), DAY(LAST_DAY(CURDATE()))), 2, '0')
               ),
               '%Y-%m-%d'
             )
          THEN 'PAID'
        WHEN pcur.paid_at IS NULL
             AND CURDATE() <= STR_TO_DATE(
               CONCAT(
                 YEAR(CURDATE()), '-',
                 LPAD(MONTH(CURDATE()), 2, '0'), '-',
                 LPAD(LEAST(DAY(c.installed_at), DAY(LAST_DAY(CURDATE()))), 2, '0')
               ),
               '%Y-%m-%d'
             )
          THEN 'ONTIME'
        WHEN pcur.paid_at IS NULL
             AND CURDATE() > STR_TO_DATE(
               CONCAT(
                 YEAR(CURDATE()), '-',
                 LPAD(MONTH(CURDATE()), 2, '0'), '-',
                 LPAD(LEAST(DAY(c.installed_at), DAY(LAST_DAY(CURDATE()))), 2, '0')
               ),
               '%Y-%m-%d'
             )
          THEN 'LATE'
        ELSE 'OTHER'
      END AS status
    FROM clients c
    LEFT JOIN payments pcur
      ON pcur.client_id = c.id
     AND pcur.due_date = STR_TO_DATE(
        CONCAT(
          YEAR(CURDATE()), '-',
          LPAD(MONTH(CURDATE()), 2, '0'), '-',
          LPAD(LEAST(DAY(c.installed_at), DAY(LAST_DAY(CURDATE()))), 2, '0')
        ),
        '%Y-%m-%d'
      )
    WHERE 1=1
  ) AS t
  WHERE 1=1
";

$params = [];
$types  = "";

// Buscador
if ($q !== '') {
  $sql .= " AND (t.name LIKE CONCAT('%', ?, '%') OR t.client_code LIKE CONCAT('%', ?, '%')) ";
  $params[] = $q; $params[] = $q; $types .= "ss";
}

// Filtro por estado
if (in_array($status, ['PAID','ONTIME','LATE'])) {
  $sql .= " AND t.status = ? ";
  $params[] = $status; $types .= "s";
}

$sql .= " ORDER BY t.name ASC ";

$stmt = $mysqli->prepare($sql);
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Clientes</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  <?php include __DIR__ . '/../includes/header.php'; ?>
  <div class="container">
    <div class="top-actions">
      <div class="left">
        <h1>Clientes (<?php echo htmlspecialchars($res->num_rows); ?>)</h1>
        <?php if ($status): ?><span class="pill"><?php echo htmlspecialchars($status); ?></span><?php endif; ?>
      </div>
      <div class="right">
        <?php if (is_admin()): ?>
          <a class="btn primary" href="client_form.php">+ Nuevo cliente</a>
        <?php endif; ?>
      </div>
    </div>

    <div class="card">
      <table>
        <thead>
          <tr>
            <th>Nombre del cliente</th>
            <th>Clave</th>
            <th>Fecha de pago (mes actual)</th>
            <th>Cuánto paga</th>
            <th></th>
            <?php if (is_admin()): ?><th></th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $res->fetch_assoc()): ?>
            <tr>
              <td><?php echo htmlspecialchars($row['name']); ?></td>
              <td><?php echo htmlspecialchars($row['client_code']); ?></td>
              <td><?php echo htmlspecialchars($row['due_current_fmt'] ?? '—'); ?></td>
              <td><?php echo htmlspecialchars($row['amount_due_current'] ?? '—'); ?></td>
              <td><a class="btn" href="client_detail.php?id=<?php echo (int)$row['id']; ?>">Historial</a></td>
              <?php if (is_admin()): ?>
                <td>
                  <a class="btn link" href="client_form.php?id=<?php echo (int)$row['id']; ?>">Editar</a>
                  <form action="../actions/client_delete.php" method="post" style="display:inline" onsubmit="return confirm('¿Eliminar cliente?');">
                    <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                    <button class="btn danger" type="submit">Eliminar</button>
                  </form>
                </td>
              <?php endif; ?>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
