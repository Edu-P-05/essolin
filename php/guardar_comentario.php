<?php
session_start();
require_once 'conexion.php';
header("Content-Type: application/json; charset=utf-8");

$data = json_decode(file_get_contents("php://input"), true);
$id_trabajo = $data['id_trabajo'] ?? '';
$comentario = $data['comentario'] ?? '';
// Asumimos que el login guarda el ID, si no, usamos 1 temporalmente para pruebas
$id_usuario = $_SESSION['id_usuario'] ?? 1; 

if (empty($id_trabajo) || empty($comentario)) {
    echo json_encode(["success" => false, "mensaje" => "Datos incompletos"]);
    exit;
}

try {
    $pdo = getConexion();
    $sql = "INSERT INTO bitacora_trabajos (id_trabajo, id_usuario, comentario) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_trabajo, $id_usuario, $comentario]);
    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "mensaje" => "Error: " . $e->getMessage()]);
}
?>