<?php
// ============================================
// CREAR HABITACION - Hotel EcoAventura
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
$numero_habitacion = trim($_POST['numero_habitacion'] ?? '');
$tipo = $_POST['tipo'] ?? 'sencilla';
$descripcion = trim($_POST['descripcion'] ?? '');
$capacidad = intval($_POST['capacidad'] ?? 2);
$precio_noche = floatval($_POST['precio_noche'] ?? 0);
$estado = $_POST['estado'] ?? 'disponible';

// Validaciones
if (empty($numero_habitacion) || $precio_noche <= 0) {
    header("Location: ../../panelAdmin.php?error=" . urlencode("Numero de habitacion y precio son requeridos"));
    exit();
}

// Validar enums
$tipos_validos = ['sencilla', 'doble', 'suite', 'familiar'];
$estados_validos = ['disponible', 'ocupada', 'mantenimiento'];

if (!in_array($tipo, $tipos_validos)) $tipo = 'sencilla';
if (!in_array($estado, $estados_validos)) $estado = 'disponible';
if ($capacidad < 1) $capacidad = 1;
if ($capacidad > 10) $capacidad = 10;

// Verificar que no exista el numero de habitacion
$stmt = $conn->prepare("SELECT id FROM habitaciones WHERE numero_habitacion = ?");
$stmt->bind_param("s", $numero_habitacion);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    header("Location: ../../panelAdmin.php?error=" . urlencode("Ya existe una habitacion con ese numero"));
    exit();
}
$stmt->close();

// Insertar habitacion
$sql = "INSERT INTO habitaciones (numero_habitacion, tipo, descripcion, capacidad, precio_noche, estado) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssids", $numero_habitacion, $tipo, $descripcion, $capacidad, $precio_noche, $estado);

if ($stmt->execute()) {
    header("Location: ../../panelAdmin.php?success=" . urlencode("Habitacion creada correctamente"));
} else {
    header("Location: ../../panelAdmin.php?error=" . urlencode("Error al crear habitacion: " . $conn->error));
}

$stmt->close();
$conn->close();
exit();
?>
