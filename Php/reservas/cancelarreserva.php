<?php
// ============================================
// CANCELAR RESERVA - Hotel EcoAventura
// ============================================
session_start();
include("../conexion.php");

// Verificar sesion
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../../login.html?error=" . urlencode("Debes iniciar sesion"));
    exit();
}

// Verificar ID
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: ../../dashboardCliente.php?error=" . urlencode("ID de reserva no valido"));
    exit();
}

// Verificar que la reserva pertenezca al usuario (a menos que sea admin)
$user_id = $_SESSION['id'];
$es_admin = ($_SESSION['rol'] === 'administrador');

if (!$es_admin) {
    $stmt = $conn->prepare("SELECT id FROM reservas WHERE id = ? AND id_usuario = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header("Location: ../../dashboardCliente.php?error=" . urlencode("No tienes permiso para cancelar esta reserva"));
        exit();
    }
    $stmt->close();
}

// Actualizar estado a cancelada (no eliminar)
$sql = "UPDATE reservas SET estado = 'cancelada' WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $redirect = $es_admin ? "../../panelAdmin.php" : "../../dashboardCliente.php";
    header("Location: $redirect?success=" . urlencode("Reserva cancelada correctamente"));
} else {
    $redirect = $es_admin ? "../../panelAdmin.php" : "../../dashboardCliente.php";
    header("Location: $redirect?error=" . urlencode("Error al cancelar: " . $conn->error));
}

$stmt->close();
$conn->close();
exit();
?>
