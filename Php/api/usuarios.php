<?php
// ============================================
// API DE USUARIOS EN TIEMPO REAL
// Hotel EcoAventura
// ============================================

header('Content-Type: application/json');
session_start();

// Verificar autenticacion
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['rol'] !== 'administrador') {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit();
}

include("../conexion.php");

$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'list':
        // Obtener todos los usuarios
        $result = $conn->query("SELECT id, nombre, apellido, email, username, rol, estado, fecha_registro, ultimo_acceso FROM usuarios ORDER BY fecha_registro DESC");
        $usuarios = [];
        while ($row = $result->fetch_assoc()) {
            $row['fecha_registro_formato'] = date('d/m/Y H:i', strtotime($row['fecha_registro']));
            $row['ultimo_acceso_formato'] = $row['ultimo_acceso'] ? date('d/m/Y H:i', strtotime($row['ultimo_acceso'])) : 'Nunca';
            $usuarios[] = $row;
        }
        echo json_encode(['success' => true, 'usuarios' => $usuarios]);
        break;
        
    case 'clientes':
        // Obtener solo clientes
        $result = $conn->query("SELECT id, nombre, apellido, email, username, estado, fecha_registro, ultimo_acceso FROM usuarios WHERE rol = 'cliente' ORDER BY fecha_registro DESC");
        $clientes = [];
        while ($row = $result->fetch_assoc()) {
            $row['fecha_registro_formato'] = date('d/m/Y H:i', strtotime($row['fecha_registro']));
            $clientes[] = $row;
        }
        echo json_encode(['success' => true, 'clientes' => $clientes]);
        break;
        
    case 'recientes':
        // Obtener usuarios registrados en las ultimas 24 horas
        $result = $conn->query("SELECT id, nombre, apellido, email, rol, fecha_registro FROM usuarios WHERE fecha_registro > DATE_SUB(NOW(), INTERVAL 24 HOUR) ORDER BY fecha_registro DESC");
        $recientes = [];
        while ($row = $result->fetch_assoc()) {
            $row['tiempo_desde'] = tiempoTranscurrido($row['fecha_registro']);
            $recientes[] = $row;
        }
        echo json_encode(['success' => true, 'recientes' => $recientes]);
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Accion no valida']);
}

$conn->close();

// Funcion para calcular tiempo transcurrido
function tiempoTranscurrido($fecha) {
    $ahora = new DateTime();
    $registro = new DateTime($fecha);
    $diff = $ahora->diff($registro);
    
    if ($diff->d > 0) return 'Hace ' . $diff->d . ' dia(s)';
    if ($diff->h > 0) return 'Hace ' . $diff->h . ' hora(s)';
    if ($diff->i > 0) return 'Hace ' . $diff->i . ' minuto(s)';
    return 'Hace un momento';
}
?>
