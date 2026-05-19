<?php
// Archivo: php/subir_evidencia.php
require_once 'conexion.php';
header("Content-Type: application/json; charset=utf-8");

if (!isset($_FILES['foto']) || empty($_POST['id_trabajo'])) {
    echo json_encode(["success" => false, "mensaje" => "Falta la imagen o el ID del trabajo."]);
    exit;
}

$id_trabajo = $_POST['id_trabajo'];
$archivo = $_FILES['foto'];

if ($archivo['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["success" => false, "mensaje" => "Error al cargar la imagen al servidor."]);
    exit;
}

// Magia aquí: Leemos el archivo físico y lo convertimos a datos binarios puros
$imagenBinaria = file_get_contents($archivo['tmp_name']);

try {
    $pdo = getConexion();
    // Guardamos el binario pesado en la columna que acabamos de modificar a LONGBLOB
    $stmt = $pdo->prepare("INSERT INTO evidencias (id_trabajo, ruta_archivo) VALUES (?, ?)");
    
    if ($stmt->execute([$id_trabajo, $imagenBinaria])) {
        echo json_encode(["success" => true, "mensaje" => "Evidencia guardada dentro de MySQL con éxito."]);
    } else {
        echo json_encode(["success" => false, "mensaje" => "Error al intentar insertar en la base de datos."]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "mensaje" => "Error SQL: " . $e->getMessage()]);
}
?>