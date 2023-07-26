<?php
// Este código se ejecuta del lado del servidor
require './common.php';
if (isset($_SESSION['id']) && isset($_SESSION['user'])) {
  // Usuario ya inició sesión, redirigir al inicio.
  header("Location: /");
}
// Por defecto, success es falso.
$success = false;
if (isset($_POST['register'])) {
  if (
    empty($_POST['email']) ||
    empty($_POST['username']) ||
    empty($_POST['password']) ||
    empty($_POST['password-confirm'])
  ) {
    $message = "Campos vacíos!";
  } else if ($_POST['password'] !== $_POST['password-confirm']) {
    $message = "Contraseñas no coinciden";
  } else {
    // Se envió el formulario. Crear registro.
    $statement = $PDO->prepare("INSERT INTO users (email, username, password) VALUES (?,?,?)");
    $statement->execute([$_POST['email'], $_POST['username'], $_POST['password']]);
    if ($result === false) {
      $message = "Ocurrió un error. Intente de nuevo";
    } else {
      // Se registran los datos. Confirmar al usuario
      $success = true;
      $message = "Registro satisfactorio";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registro</title>
  <link rel="stylesheet" href="./styles/bootstrap.min.css">
</head>

<body>
  <div class="container">
    <div class="row">
      <h2>Registrar</h2>
      <?php
      if (isset($message) && $message !== '') {
        echo "<div class=\"alert " . (($success) ? "alert-info" : "alert-danger") . "\" role=\"alert\">{$message}</div>";
      }
      ?>
      <form method="post" action="/register.php">
        <input type="hidden" name="register" value="register">
        <div class="mb-3">
          <label for="email" class="form-label">Correo</label>
          <input type="email" class="form-control" id="email" name="email">
        </div>
        <div class="mb-3">
          <label for="username" class="form-label">Usuario</label>
          <input type="text" class="form-control" id="username" name="username">
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Contraseña</label>
          <input type="password" class="form-control" id="password" name="password">
        </div>
        <div class="mb-3">
          <label for="password-confirm" class="form-label">Confirmar contraseña</label>
          <input type="password" class="form-control" id="password-confirm" name="password-confirm">
        </div>
        <button type="submit" class="btn btn-primary w-100 my-2">Registrar</button>
        <a href="/login.php" class="btn btn-secondary w-100">Ya tiene cuenta? Iniciar sesión</a>

      </form>
    </div>
  </div>
</body>

</html>