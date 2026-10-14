<?php
// ============================================
// ACTUALIZAR HABITACION - Hotel EcoAventura
// ============================================
session_start();
include("../conexion.php");

// Verificar sesion admin
if (!isset($_SESSION['loggedin']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: ../../panelAdmin.php?error=" . urlencode("Acceso no autorizado"));
    exit();
}

// Verificar metodo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../panelAdmin.php?error=" . urlencode("Metodo no permitido"));
    exit();
}

// Obtener datos
$id = intval($_POST['id'] ?? 0);
$numero_habitacion = trim($_POST['numero_habitacion'] ?? '');
$tipo = $_POST['tipo'] ?? 'sencilla';
$capacidad = intval($_POST['capacidad'] ?? 2);
$precio_noche = floatval($_POST['precio_noche'] ?? 0);
$estado = $_POST['estado'] ?? 'disponible';

// Validaciones
if ($id <= 0 || empty($numero_habitacion) || $precio_noche <= 0) {
    header("Location: ../../panelAdmin.php?error=" . urlencode("Datos incompletos"));
    exit();
}

// Validar enums
$tipos_validos = ['sencilla', 'doble', 'suite', 'familiar'];
$estados_validos = ['disponible', 'ocupada', 'mantenimiento'];

if (!in_array($tipo, $tipos_validos)) $tipo = 'sencilla';
if (!in_array($estado, $estados_validos)) $estado = 'disponible';

// Verificar que el numero no este en uso por otra habitacion
$stmt = $conn->prepare("SELECT id FROM habitaciones WHERE numero_habitacion = ? AND id != ?");
$stmt->bind_param("si", $numero_habitacion, $id);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    header("Location: ../../panelAdmin.php?error=" . urlencode("Ya existe otra habitacion con ese numero"));
    exit();
}
$stmt->close();

// Actualizar habitacion
$sql = "UPDATE habitaciones SET numero_habitacion = ?, tipo = ?, capacidad = ?, precio_noche = ?, estado = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssidsi", $numero_habitacion, $tipo, $capacidad, $precio_noche, $estado, $id);

if ($stmt->execute()) {
    header("Location: ../../panelAdmin.php?success=" . urlencode("Habitacion actualizada correctamente"));
} else {
    header("Location: ../../panelAdmin.php?error=" . urlencode("Error al actualizar: " . $conn->error));
}

$stmt->close();
$conn->close();
exit();
?>
