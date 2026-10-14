<?php
// ============================================
// ELIMINAR HABITACION - Hotel EcoAventura
// ============================================
session_start();
include("../conexion.php");

// Verificar sesion admin
if (!isset($_SESSION['loggedin']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: ../../panelAdmin.php?error=" . urlencode("Acceso no autorizado"));
    exit();
}

// Verificar ID
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: ../../panelAdmin.php?error=" . urlencode("ID no valido"));
    exit();
}

// Verificar si tiene reservas activas
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM reservas WHERE id_habitacion = ? AND estado IN ('pendiente', 'confirmada')");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if ($result['total'] > 0) {
    header("Location: ../../panelAdmin.php?error=" . urlencode("No se puede eliminar: tiene reservas activas"));
    exit();
}
$stmt->close();

// Eliminar habitacion
$sql = "DELETE FROM habitaciones WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: ../../panelAdmin.php?success=" . urlencode("Habitacion eliminada correctamente"));
} else {
    header("Location: ../../panelAdmin.php?error=" . urlencode("Error al eliminar: " . $conn->error));
}

$stmt->close();
$conn->close();
exit();
?>
