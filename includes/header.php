<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<header class="app-header">
  <div class="right">
    <a class="brand" href="dashboard.php">FTTH</a>
    <nav>
      <a href="dashboard.php">Graficos</a>
      <a href="clients.php">General</a>
    </nav>
  </div>
  <div class="right">
    <form action="clients.php" method="get" class="search-form">
      <input type="text" name="q" placeholder="Buscar cliente o clave..." value="<?php echo isset($_GET['q'])? htmlspecialchars($_GET['q']) : '';?>">
      <button type="submit" title="Buscar" class="icon-btn" aria-label="Buscar">🔎</button>
    </form>
    <div class="user">
      <a class="btn-logout" href="logout.php">Salir</a>
    </div>
  </div>
</header>
