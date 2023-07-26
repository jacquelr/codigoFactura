<?php
require './common.php';
require './phpmailer/Exception.php';
require './phpmailer/PHPMailer.php';
require './phpmailer/SMTP.php';
require './TCPDF/tcpdf.php';

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

$mail = new \PHPMailer\PHPMailer\PHPMailer(true);
try {
  // Server
  $mail->isSMTP();
  $mail->Host       = 'smtp.gmail.com';
  $mail->SMTPAuth   = true;
  $mail->Username   = 'jlrios2001@gmail.com';
  $mail->Password   = 'qvkxoyzaoizrckuf';
  $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
  $mail->Port       = 465;

  //Recipients
  $mail->setFrom('jlrios2001@gmail.com', 'Servidor');
  $statement = $PDO->prepare("SELECT email FROM users WHERE id = ?");
  $statement->execute([$_SESSION['id']]);
  $email = $statement->fetchAll(PDO::FETCH_ASSOC)[0]['email'];

  $mail->addAddress($email);
  // Si quieres depurar, hardcoded tu correo
  // $mail->AddAddress("mdlb.lobo@gmail.com");
  $mail->addReplyTo('jlrios2001@gmail.com', 'Servidor');

  // Body

  // Crea una instancia de TCPDF
  $pdf = new TCPDF();

  // Agrega una nueva página
  $pdf->AddPage();

  // Agrega contenido al PDF
  $pdf->SetFont('times', '', 12);
  $pdf->Cell(0, 10, 'Detalles de la compra', 0, 1);

  // ... (Agrega aquí el contenido específico de la compra como en tu ejemplo)
  $statement = $PDO->prepare(<<<SQL
    SELECT 
        products.id, products.name, products.price, cart_has_products.quantity 
    FROM cart_has_products  
    INNER JOIN products ON cart_has_products.product_id = products.id
    WHERE cart_id = ?
  SQL);
  $statement->execute([$cart['id']]);
  $result = $statement->fetchAll(PDO::FETCH_ASSOC);

  // Agrega la tabla al PDF
  $pdf->SetFont('times', '', 12);
  $pdf->SetFillColor(200, 200, 200); // Color de fondo de las celdas de encabezado
  $pdf->Cell(30, 10, '#', 1, 0, 'C', 1);
  $pdf->Cell(60, 10, 'Producto', 1, 0, 'C', 1);
  $pdf->Cell(40, 10, 'Precio unitario', 1, 0, 'C', 1);
  $pdf->Cell(40, 10, 'Cantidad', 1, 0, 'C', 1);
  $pdf->Cell(40, 10, 'Precio total', 1, 1, 'C', 1);

  $granTotal = 0;
  foreach ($result as $key => $row) {
    $key = $key + 1;
    $name = (string)$row['name'];
    $unitPrice = (float)$row['price'];
    $quantity = (int)$row['quantity'];
    $total = $unitPrice * $quantity;
    $granTotal = $granTotal + $total;

    // Agrega una fila a la tabla en el PDF
    $pdf->Cell(30, 10, $key, 1, 0, 'C');
    $pdf->Cell(60, 10, $name, 1, 0, 'C');
    $pdf->Cell(40, 10, $unitPrice, 1, 0, 'C');
    $pdf->Cell(40, 10, $quantity, 1, 0, 'C');
    $pdf->Cell(40, 10, $total, 1, 1, 'C');
  }

  // Agrega el total al PDF
  $pdf->Cell(170, 10, 'Total:', 1, 0, 'R');
  $pdf->Cell(40, 10, $granTotal, 1, 1, 'C');


  // Genera el contenido del PDF
  $contenido_pdf = $pdf->Output('', 'S');

  // Cierra el documento PDF para liberar recursos
  $pdf->close();

  // Adjuntar el archivo PDF al correo electrónico
  $mail->addStringAttachment($contenido_pdf, 'compra.pdf');

  //Fin PDF

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
  $HTML = <<<HTML
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
  HTML;
  $granTotal = 0;
  foreach ($result as $key => $row) {
    $key = $key + 1;
    $name = (string)$row['name'];
    $unitPrice = (float)$row['price'];
    $quantity = (int)$row['quantity'];
    $total = $unitPrice * $quantity;
    $granTotal = $granTotal + $total;
    $HTML = $HTML . <<<HTML
    <tr>
      <th scope="row">$key</th>
      <td>$name</td>
      <td>$unitPrice</td>
      <td>$quantity</td>
      <td>$total</td>
    </tr>
    HTML;
  }
  $HTML = $HTML . '
              </tbody>
            </table>
            <h4>Total: ' . $granTotal . '</h4>';

  //Content
  $mail->isHTML(true);
  $mail->Subject = 'Recibo de compra';
  $mail->Body    = $HTML;
  $mail->AltBody = 'Se requiere de HTML :c';

  $mail->send();

  $statement = $PDO->prepare('DELETE FROM cart_has_products where cart_id = ?');
  $statement->execute([$cart['id']]);
} catch (\Exception $e) {
  die('No se envió correo. Razón: ' . $e->getMessage());
}

header("Location: /");
