<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  <div class="container" style="max-width:520px;">
    <div class="card">
      <h1>Ingresar</h1>
      <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
      <?php endif; ?>
      <form method="post" action="../actions/login.php">
        <div class="row">
          <div>
            <label for="email">Correo</label>
            <input type="email" id="email" name="email" required>
          </div>
          <div>
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required>
          </div>
        </div>
        <div style="margin-top:12px;">
          <button class="btn primary" type="submit">Entrar</button>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
