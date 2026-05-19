<?php
// Archivo: php/actualizar_claves.php
require_once 'conexion.php';

$pdo = getConexion();
$password_real = 'essolin123';

// Generar el hash real en tu computadora
$hash_correcto = password_hash($password_real, PASSWORD_BCRYPT);

// Actualizar la contraseña de TODOS los usuarios en la base de datos
$stmt = $pdo->prepare("UPDATE usuarios SET password = ?");

if ($stmt->execute([$hash_correcto])) {
    echo "¡Éxito! Las contraseñas de todos los usuarios (Admin, Supervisor, Técnico y Secretaria) han sido actualizadas a 'essolin123'.";
} else {
    echo "Hubo un error al actualizar la base de datos.";
}
?>