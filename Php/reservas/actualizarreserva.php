<?php
// ============================================
// ACTUALIZAR ESTADO RESERVA - Hotel EcoAventura
// ============================================
session_start();
include("../conexion.php");

// Verificar sesion admin
if (!isset($_SESSION['loggedin']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: ../../panelAdmin.php?error=" . urlencode("Acceso no autorizado"));
    exit();
}

// Verificar datos
$id = intval($_GET['id'] ?? 0);
$estado = $_GET['estado'] ?? '';

if ($id <= 0) {
    header("Location: ../../panelAdmin.php?error=" . urlencode("ID de reserva no valido"));
    exit();
}

// Validar estado
$estados_validos = ['pendiente', 'confirmada', 'completada', 'cancelada'];
if (!in_array($estado, $estados_validos)) {
    header("Location: ../../panelAdmin.php?error=" . urlencode("Estado no valido"));
    exit();
}

// Actualizar estado
$sql = "UPDATE reservas SET estado = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $estado, $id);

if ($stmt->execute()) {
    header("Location: ../../panelAdmin.php?success=" . urlencode("Estado de reserva actualizado"));
} else {
    header("Location: ../../panelAdmin.php?error=" . urlencode("Error al actualizar: " . $conn->error));
}

$stmt->close();
$conn->close();
exit();
?>
