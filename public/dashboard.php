<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

/*
Estados SIN días de gracia:
- PAID: paid_at <= due_date
- ONTIME: no pagado y hoy <= due_date
- LATE: no pagado y hoy > due_date
*/
$sql = "
  SELECT status, COUNT(*) as total FROM (
    SELECT
      p.id,
      CASE
        WHEN p.paid_at IS NOT NULL AND DATE(p.paid_at) <= DATE(p.due_date) THEN 'PAID'
        WHEN p.paid_at IS NULL AND CURDATE() <= DATE(p.due_date) THEN 'ONTIME'
        WHEN p.paid_at IS NULL AND CURDATE() >  DATE(p.due_date) THEN 'LATE'
        ELSE 'OTHER'
      END AS status
    FROM payments p
  ) t
  GROUP BY status
";
$res = $mysqli->query($sql);
$counts = ['PAID'=>0,'ONTIME'=>0,'LATE'=>0];
while ($row = $res->fetch_assoc()) {
  if (isset($counts[$row['status']])) $counts[$row['status']] = (int)$row['total'];
}
$total = array_sum($counts);
function pct($n,$t){ return $t>0 ? round(($n/$t)*100) : 0; }
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard — Client Manager</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <?php include __DIR__ . '/../includes/header.php'; ?>
  <div class="container">
    <div class="grid cols-3">
      <div class="card">
        <h2>Pagos al día</h2>
        <div class="pill ok"><?php echo pct($counts['ONTIME'],$total); ?>% al día</div>
        <p class="muted">Pendientes antes del vencimiento.</p>
      </div>
      <div class="card">
        <h2>Pagos realizados</h2>
        <div class="pill ok"><?php echo pct($counts['PAID'],$total); ?>% pagados</div>
        <p class="muted">Pagos liquidados a tiempo.</p>
      </div>
      <div class="card">
        <h2>Pagos en retraso</h2>
        <div class="pill bad"><?php echo pct($counts['LATE'],$total); ?>% en retraso</div>
        <p class="muted">Vencidos sin pago.</p>
      </div>
    </div>

    <div class="card">
      <h2>Resumen general</h2>
      <canvas id="statusChart" height="100px"></canvas>
      <p class="muted">Haz clic en una sección para ver el detalle.</p>
    </div>
  </div>

  <script>
  const data = {
    labels: ['Pagados','Al día','En retraso'],
    datasets: [{ data: [<?= (int)$counts['PAID'] ?>, <?= (int)$counts['ONTIME'] ?>, <?= (int)$counts['LATE'] ?>] }]
  };
  const chart = new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data,
    options: {
      onClick: (evt, els) => {
        if (!els.length) return;
        const map = {0:'PAID',1:'ONTIME',2:'LATE'};
        window.location.href = 'clients.php?status=' + map[els[0].index];
      }
    }
  });
  </script>
</body>
</html>
