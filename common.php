<?php

// Código compartido entre todos los scrips. Aquí se maneja el estado de la sesión y variables globales.
session_start();
/** @var \PDO */
$PDO = new PDO('mysql:dbname=factura;host=127.0.0.1', 'factura', 'factura');
