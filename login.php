<?php
// Este código se ejecuta del lado del servidor
require './common.php';
if (isset($_SESSION['id']) && isset($_SESSION['user'])) {
  // Usuario ya inició sesión, redirigir al inicio.
  header("Location: /");
}
if (isset($_POST['login'])) {
  // No permitir campos vacíos
  if (empty($_POST['username']) || empty($_POST['password'])) {
    $error = "Campos vacíos!";
  } else {
    // Se envió el formulario. Verificar credenciales.
    $statement = $PDO->prepare("SELECT * FROM users WHERE `username` = ? AND `password` = ?");
    $statement->execute([$_POST['username'], $_POST['password']]);
    $result = $statement->fetchAll();
    if (empty($result)) {
      $error = "Usuario/Contraseña incorrecto";
    } else {
      // La sesión siempre esta iniciada. Por tanto persisten estos datos.
      $_SESSION['user'] = $_POST['username'];
      $_SESSION['id'] = $result[0]['id'];
      // Redirigir al inicio
      header("Location: /");
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link rel="stylesheet" href="./styles/bootstrap.min.css">
</head>

<body>
  <div class="container">
    <div class="row">
      <!-- El formulario se procesa en este mismo archivo -->
      <h2>Iniciar sesión</h2>
      <?php if (isset($error) && $error !== '') echo "<div class=\"alert alert-danger\" role=\"alert\">{$error}</div>"; ?>
      <form method="post" action="/login.php">
        <input type="hidden" name="login" value="login">
        <div class="mb-3">
          <label for="username" class="form-label">Usuario</label>
          <input type="text" class="form-control" id="username" name="username">
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Contraseña</label>
          <input type="password" class="form-control" id="password" name="password">
        </div>
        <button type="submit" class="btn btn-primary w-100 my-2">Iniciar</button>
        <a href="/register.php" class="btn btn-secondary w-100">Sin cuenta? Regístrate</a>
      </form>
    </div>
  </div>
</body>

</html>