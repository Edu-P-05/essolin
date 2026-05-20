<?php
define('DB_HOST',   'localhost');   // Siempre "localhost" en XAMPP
define('DB_USER',   'root');        // Usuario por defecto de XAMPP
define('DB_PASS',   '');            // Contraseña vacía por defecto en XAMPP
define('DB_NAME',   'essolin_db');  // Nombre de la base de datos

// Crear conexión PDO (más segura que mysqli)
function getConexion(): PDO {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["success" => false, "mensaje" => "Error de conexión: " . $e->getMessage()]);
        exit;
    }
}

// Cabeceras para permitir peticiones desde el navegador (CORS local)
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
?>