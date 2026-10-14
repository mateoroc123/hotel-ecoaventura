<?php
// ============================================
// ELIMINAR USUARIO - Hotel EcoAventura
// ============================================
session_start();
include("conexion.php");

// Verificar sesion admin
if (!isset($_SESSION['loggedin']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: ../panelAdmin.php?error=" . urlencode("Acceso no autorizado"));
    exit();
}

// Verificar ID
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: ../panelAdmin.php?error=" . urlencode("ID no valido"));
    exit();
}

// No permitir eliminar el propio usuario
if ($id == $_SESSION['id']) {
    header("Location: ../panelAdmin.php?error=" . urlencode("No puedes eliminar tu propia cuenta"));
    exit();
}

// No permitir eliminar administradores
$checkAdmin = $conn->prepare("SELECT rol FROM usuarios WHERE id = ?");
$checkAdmin->bind_param("i", $id);
$checkAdmin->execute();
$result = $checkAdmin->get_result();
$userToDelete = $result->fetch_assoc();
$checkAdmin->close();

if ($userToDelete && $userToDelete['rol'] === 'administrador') {
    header("Location: ../panelAdmin.php?error=" . urlencode("No puedes eliminar a otros administradores"));
    exit();
}

// Eliminar usuario
$sql = "DELETE FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: ../panelAdmin.php?success=" . urlencode("Usuario eliminado correctamente"));
} else {
    header("Location: ../panelAdmin.php?error=" . urlencode("Error al eliminar: " . $conn->error));
}

$stmt->close();
$conn->close();
exit();
?>
