<?php
// ============================================
// CREAR RESERVA - Hotel EcoAventura
// ============================================
session_start();
include("../conexion.php");

// Verificar sesion
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../../login.html?error=" . urlencode("Debes iniciar sesion"));
    exit();
}

// Verificar metodo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../dashboardCliente.php?error=" . urlencode("Metodo no permitido"));
    exit();
}

// Obtener datos
$id_usuario = $_SESSION['id'];
$id_habitacion = intval($_POST['id_habitacion'] ?? 0);
$fecha_entrada = $_POST['fecha_entrada'] ?? '';
$fecha_salida = $_POST['fecha_salida'] ?? '';
$num_huespedes = intval($_POST['num_huespedes'] ?? 1);
$notas = trim($_POST['notas'] ?? '');
$precio_noche = floatval($_POST['precio_noche'] ?? 0);

// Validaciones basicas
if ($id_habitacion <= 0 || empty($fecha_entrada) || empty($fecha_salida)) {
    header("Location: ../../dashboardCliente.php?error=" . urlencode("Datos de reserva incompletos"));
    exit();
}

// Validar fechas
$entrada = new DateTime($fecha_entrada);
$salida = new DateTime($fecha_salida);
$hoy = new DateTime();

if ($entrada < $hoy) {
    header("Location: ../../dashboardCliente.php?error=" . urlencode("La fecha de entrada no puede ser anterior a hoy"));
    exit();
}

if ($salida <= $entrada) {
    header("Location: ../../dashboardCliente.php?error=" . urlencode("La fecha de salida debe ser posterior a la entrada"));
    exit();
}

// Calcular noches y precio total
$diff = $entrada->diff($salida);
$noches = $diff->days;

// Obtener precio de la habitacion si no viene
if ($precio_noche <= 0) {
    $stmt = $conn->prepare("SELECT precio_noche FROM habitaciones WHERE id = ?");
    $stmt->bind_param("i", $id_habitacion);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $precio_noche = $result->fetch_assoc()['precio_noche'];
    }
    $stmt->close();
}

$precio_total = $noches * $precio_noche;

// Verificar disponibilidad (que no haya otra reserva para esas fechas)
$stmt = $conn->prepare("
    SELECT id FROM reservas 
    WHERE id_habitacion = ? 
    AND estado IN ('pendiente', 'confirmada')
    AND ((fecha_entrada <= ? AND fecha_salida > ?) 
         OR (fecha_entrada < ? AND fecha_salida >= ?)
         OR (fecha_entrada >= ? AND fecha_salida <= ?))
");
$stmt->bind_param("issssss", $id_habitacion, $fecha_salida, $fecha_entrada, $fecha_salida, $fecha_entrada, $fecha_entrada, $fecha_salida);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    header("Location: ../../dashboardCliente.php?error=" . urlencode("La habitacion no esta disponible para esas fechas"));
    exit();
}
$stmt->close();

// Insertar reserva
$sql = "INSERT INTO reservas (id_usuario, id_habitacion, fecha_entrada, fecha_salida, num_huespedes, precio_total, estado, notas) 
        VALUES (?, ?, ?, ?, ?, ?, 'pendiente', ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iissids", $id_usuario, $id_habitacion, $fecha_entrada, $fecha_salida, $num_huespedes, $precio_total, $notas);

if ($stmt->execute()) {
    header("Location: ../../dashboardCliente.php?success=" . urlencode("Reserva creada exitosamente. Total: $" . number_format($precio_total, 0, ',', '.')));
} else {
    header("Location: ../../dashboardCliente.php?error=" . urlencode("Error al crear reserva: " . $conn->error));
}

$stmt->close();
$conn->close();
exit();
?>
