<?php
session_start();
// Rutas exactas como me pediste
require_once '../php/conexion.php';
require_once '../DAO/CuadrillaDAO.php';

// Leer los datos JSON que envía JavaScript
$datosJSON = json_decode(file_get_contents('php://input'), true);
$datos = is_array($datosJSON) ? $datosJSON : $_POST;

$accion = $_GET['accion'] ?? '';

switch ($accion) {
    case 'listar':
        // Asegúrate de que las rutas sean correctas
        require_once '../php/conexion.php'; 
        require_once '../DAO/CuadrillaDAO.php';
        
        try {
            // 1. Llamamos a la función de tu archivo conexion.php
            $pdo = getConexion(); 
            
            // 2. Instanciamos el DAO pasando el objeto PDO
            $cuadrillaDAO = new CuadrillaDAO($pdo);
            
            // 3. Traemos los datos reales
            $lista = $cuadrillaDAO->listarTodas();
            
            echo json_encode(["success" => true, "data" => $lista]);
        } catch (Exception $e) {
            echo json_encode(["success" => false, "mensaje" => "Error de BD: " . $e->getMessage()]);
        }
        break;
    case 'guardar':
        require_once '../php/conexion.php';
        require_once '../DAO/CuadrillaDAO.php';
        
        $pdo = getConexion();
        $cuadrillaDAO = new CuadrillaDAO($pdo);

        // Capturamos los datos
        $id = $datos['id_cuadrilla'] ?? ''; 
        $nombre = $datos['nombre'] ?? '';
        $id_supervisor = $datos['id_supervisor'] ?? '';
        $tecnicos = $datos['tecnicos'] ?? []; // Array de IDs de técnicos

        if ($cuadrillaDAO->guardar($id, $nombre, $id_supervisor, $tecnicos)) {
            echo json_encode(["success" => true, "mensaje" => "Cuadrilla guardada."]);
        } else {
            echo json_encode(["success" => false, "mensaje" => "Error al guardar en BD."]);
        }
        break;

    case 'eliminar':
        require_once '../php/conexion.php';
        require_once '../DAO/CuadrillaDAO.php';
        
        $pdo = getConexion();
        $cuadrillaDAO = new CuadrillaDAO($pdo);
        
        // Obtenemos el ID que enviamos desde el JS
        $id = $datos['id_cuadrilla'] ?? '';
        
        if ($cuadrillaDAO->eliminar($id)) {
            echo json_encode(["success" => true, "mensaje" => "Cuadrilla eliminada correctamente."]);
        } else {
            echo json_encode(["success" => false, "mensaje" => "Error al eliminar."]);
        }
        break;

    case 'datos_form':
        require_once '../php/conexion.php';
        $pdo = getConexion();
        
        // Obtenemos el ID de cuadrilla por GET (si es 0, estamos creando una nueva)
        $id_cuadrilla = $_GET['id_cuadrilla'] ?? 0;

        // 1. Supervisores
        $stmtSup = $pdo->query("SELECT id_usuario, nombre_completo FROM usuarios WHERE id_rol = 2");
        $supervisores = $stmtSup->fetchAll(PDO::FETCH_ASSOC);

        // 2. Técnicos: Disponibles o ya asignados a esta cuadrilla (si es edición)
        // REEMPLAZA EL 3 POR EL ID REAL DE TU ROL DE TÉCNICO
        $sqlTec = "SELECT id_usuario, nombre_completo 
                   FROM usuarios 
                   WHERE id_rol = 3 
                   AND (
                       id_usuario NOT IN (SELECT id_usuario FROM cuadrilla_tecnicos) 
                       OR 
                       id_usuario IN (SELECT id_usuario FROM cuadrilla_tecnicos WHERE id_cuadrilla = :id)
                   )";
        
        $stmtTec = $pdo->prepare($sqlTec);
        $stmtTec->execute(['id' => $id_cuadrilla]);
        $tecnicos = $stmtTec->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "success" => true, 
            "supervisores" => $supervisores, 
            "tecnicos" => $tecnicos
        ]);
        break;

    default:
        echo json_encode(["success" => false, "mensaje" => "Acción no válida."]);
        break;
}
?>