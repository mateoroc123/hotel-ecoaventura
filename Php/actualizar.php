<?php
// ============================================
// ACTUALIZAR USUARIO - Hotel EcoAventura
// ============================================
session_start();
include("conexion.php");

// Verificar sesion admin
if (!isset($_SESSION['loggedin']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: ../panelAdmin.php?error=" . urlencode("Acceso no autorizado"));
    exit();
}

// Verificar metodo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../panelAdmin.php?error=" . urlencode("Metodo no permitido"));
    exit();
}

// Obtener datos
$id = intval($_POST['id'] ?? 0);
$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$email = trim($_POST['email'] ?? '');
$rol = $_POST['rol'] ?? 'cliente';
$estado = $_POST['estado'] ?? 'activo';

// Validaciones basicas
if ($id <= 0 || empty($nombre) || empty($apellido) || empty($email)) {
    header("Location: ../panelAdmin.php?error=" . urlencode("Datos incompletos"));
    exit();
}

// Validar rol y estado
$roles_validos = ['cliente', 'administrador'];
$estados_validos = ['activo', 'inactivo'];

if (!in_array($rol, $roles_validos)) $rol = 'cliente';
if (!in_array($estado, $estados_validos)) $estado = 'activo';

// Verificar que el email no este en uso por otro usuario
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
$stmt->bind_param("si", $email, $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    header("Location: ../panelAdmin.php?error=" . urlencode("El email ya esta en uso"));
    exit();
}
$stmt->close();

// Actualizar usuario
$sql = "UPDATE usuarios SET nombre = ?, apellido = ?, email = ?, rol = ?, estado = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssi", $nombre, $apellido, $email, $rol, $estado, $id);

if ($stmt->execute()) {
    header("Location: ../panelAdmin.php?success=" . urlencode("Usuario actualizado correctamente"));
} else {
    header("Location: ../panelAdmin.php?error=" . urlencode("Error al actualizar: " . $conn->error));
}

$stmt->close();
$conn->close();
exit();
?>
