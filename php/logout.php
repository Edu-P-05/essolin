<?php
// Archivo: php/logout.php
session_start();
session_destroy(); // Destruye todas las variables de sesión del servidor
header("Location: ../Vistas/index2.html"); // Redirige de vuelta al login
exit;
?>