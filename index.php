<?php
require './common.php';
require './phpmailer/Exception.php';
require './phpmailer/PHPMailer.php';
require './phpmailer/SMTP.php';
if (!isset($_SESSION['id']) || !isset($_SESSION['user'])) {
  // Usuario no ha iniciado sesión, redirigir a login.
  header("Location: /login.php");
}

$success = true;
$statement = $PDO->prepare("SELECT * FROM `cart` WHERE `user_id` = ? LIMIT 1");
$statement->execute([$_SESSION['id']]);
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
if (empty($result)) {
  $statement = $PDO->prepare("INSERT INTO `cart` (user_id) VALUES (?)");
  $statement->execute([$_SESSION['id']]);
  $statement = $PDO->prepare("SELECT * FROM `cart` WHERE `user_id` = ? LIMIT 1");
  $statement->execute([$_SESSION['id']]);
  $result = $statement->fetchAll(PDO::FETCH_ASSOC);
}
$cart = $result[0];

if (isset($_POST['addCart']) && isset($_POST['id']) && isset($_POST['cantidad'])) {
  if (!empty($_POST['cantidad'])) {
    $statement = $PDO->prepare("INSERT INTO `cart_has_products` (cart_id, product_id, quantity) VALUES (?,?,?);");
    $statement->execute([$cart['id'], $_POST['id'], $_POST['cantidad']]);
  }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Productos</title>
  <link rel="stylesheet" href="./styles/bootstrap.min.css">
</head>

<body>
  <div class="container">
    <div class="row">
      <div class="col">
        <h2>Iniciaste sesión como: <?php echo $_SESSION['user'] ?></h2>
      </div>
      <div class="col">
        <form method="post" action="/logout.php">
          <input type="hidden" name="logout" value="logout">
          <button type="submit" class="btn btn-primary">Cerrar sesión</button>
        </form>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <h2>Productos disponibles</h2>
        <table class="table">
          <thead>
            <tr>
              <th scope="col">ID</th>
              <th scope="col">Producto</th>
              <th scope="col">Precio</th>
              <th scope="col">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $statement = $PDO->prepare("SELECT * FROM products;");
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result as $row) {
              $id = $row['id'];
              $name = $row['name'];
              $price = $row['price'];
              $actions = <<<HTML
                  <form method="post" action="/">
                    <input type="hidden" name="addCart" value="addCart">
                    <div class="row g-1 align-items-center">
                      <input type="hidden" name="id" value="$id">
                      <div class="col-auto">
                        <label for="cantidad" class="col-form-label">Cantidad</label>
                      </div>
                      <div class="col-auto">
                        <input type="number" name="cantidad" id="cantidad" class="form-control" >
                      </div>
                      <div class="col-auto">
                        <input type="submit" class="btn btn-primary form-control" value="Agregar al carrito">
                      </div>
                    </div>
                  </form>
                HTML;
              echo <<<HTML
                  <tr>
                    <th scope="row">$id</th>
                    <td>$name</td>
                    <td>$price</td>
                    <td>$actions</td>
                  </tr>
                HTML;
            }
            ?>
          </tbody>
        </table>
      </div>
      <div class="col">
        <h2>Tu carrito</h2>
        <?php
        $statement = $PDO->prepare(<<<SQL
            SELECT 
              products.id, products.name, products.price, cart_has_products.quantity 
            from cart_has_products  
            INNER JOIN products
            ON cart_has_products.product_id = products.id
            WHERE cart_id = ?
          SQL);
        $statement->execute([$cart['id']]);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        if (empty($result)) {
          echo "<h3>Tu carrito esta vacío</h3>";
        } else {
        ?>
          <h3>Resumen de tu pedido</h3>
          <table class="table">
            <thead>
              <tr>
                <th scope="col">#</th>
                <th scope="col">Producto</th>
                <th scope="col">Precio unitario</th>
                <th scope="col">Cantidad</th>
                <th scope="col">Precio total</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $granTotal = 0;
              foreach ($result as $key => $row) {
                $key = $key + 1;
                $name = (string)$row['name'];
                $unitPrice = (float)$row['price'];
                $quantity = (int)$row['quantity'];
                $total = $unitPrice * $quantity;
                $granTotal = $granTotal + $total;
                echo <<<HTML
                    <tr>
                      <th scope="row">$key</th>
                      <td>$name</td>
                      <td>$unitPrice</td>
                      <td>$quantity</td>
                      <td>$total</td>
                    </tr>
                  HTML;
              }
              ?>

            </tbody>
          </table>
          <h4>Total: <?php echo $granTotal; ?></h4>
          <form action="/sendEmail.php" method="post">
            <input type="hidden" name="sendEmail" value="sendEmail">
            <input type="submit" value="Comprar">
          </form>
        <?php
        }
        ?>
      </div>
    </div>
  </div>
  <script src="./js/bootstrap.bundle.min.js"></script>
</body>

</html>