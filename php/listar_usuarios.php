<?php
session_start();
require_once 'conexion.php';
header("Content-Type: application/json; charset=utf-8");

// Rescatamos el ID del usuario que está navegando actualmente (Tú)
// Nota: Asegúrate de que en tu archivo login.php estés guardando este dato en la sesión
$mi_id = $_SESSION['id_usuario'] ?? 0;

try {
    $pdo = getConexion();
    
    $sql = "SELECT 
                id_usuario, 
                nombre_completo, 
                email AS usuario, 
                CASE 
                    WHEN id_rol = 1 THEN 'Administrador'
                    WHEN id_rol = 2 THEN 'Supervisor'
                    WHEN id_rol = 3 THEN 'Tecnico'
                    WHEN id_rol = 4 THEN 'Secretaria'
                    ELSE 'Otro'
                END AS rol,
                estado 
            FROM usuarios 
            ORDER BY id_usuario DESC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($usuarios as &$u) {
        $u['nombre_completo'] = htmlspecialchars($u['nombre_completo'], ENT_QUOTES, 'UTF-8');
        $u['usuario'] = htmlspecialchars($u['usuario'], ENT_QUOTES, 'UTF-8');
        
        // NUEVO CANDADO LOGICO: Evaluamos si el usuario de esta fila soy yo mismo
        $u['es_yo'] = ($u['id_usuario'] == $mi_id);
    }
    unset($u);

    echo json_encode(["success" => true, "data" => $usuarios]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "mensaje" => "Error BD: " . $e->getMessage()]);
}
?>