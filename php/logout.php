<?php
session_start();
// Destruimos la sesión
session_unset();
session_destroy();

// Redirigimos al usuario al login
header("Location: ../index2.html");
exit();
?>