<?php
require "./common.php";
if (!isset($_SESSION['id']) || !isset($_SESSION['user'])) {
  // Usuario no ha iniciado sesión, redirigir a login.
  header("Location: /login.php");
} else if (isset($_POST['logout'])) {
  // Borrar la sesión 
  session_destroy();
  header("Location: /login.php");
} else {
  // En teoría nunca debe de pasar aquí, pero por si las dudas, redirigir a login
  header("Location: /login.php");
}
