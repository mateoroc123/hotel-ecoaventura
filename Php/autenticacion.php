<?php
// ============================================
// AUTENTICACION DE USUARIO (LOGIN)
// Hotel EcoAventura
// ============================================

session_start();
include("conexion.php");

// Verificar que sea metodo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../login.html?error=" . urlencode("Acceso no permitido"));
    exit();
}

// Recibir datos del formulario
$usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// ============================================
// VALIDACIONES
// ============================================

// Validar campos vacios
if (empty($usuario) || empty($password)) {
    header("Location: ../login.html?error=" . urlencode("Usuario y contrasena son requeridos"));
    exit();
}

// ============================================
// BUSCAR USUARIO (por email O username)
// ============================================

// Preparar consulta - buscar por email o username
$sql = "SELECT id, nombre, apellido, email, username, password, rol, estado, genero 
        FROM usuarios 
        WHERE email = ? OR username = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    header("Location: ../login.html?error=" . urlencode("Error de base de datos"));
    exit();
}

$stmt->bind_param("ss", $usuario, $usuario);
$stmt->execute();
$result = $stmt->get_result();

// Verificar si existe el usuario
if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    header("Location: ../login.html?error=" . urlencode("Usuario o contrasena incorrectos"));
    exit();
}

$user = $result->fetch_assoc();

// ============================================
// VERIFICAR CONTRASENA
// ============================================

// Verificar usando password_verify de PHP
$password_valida = password_verify($password, $user['password']);

if (!$password_valida) {
    $stmt->close();
    $conn->close();
    header("Location: ../login.html?error=" . urlencode("Usuario o contrasena incorrectos"));
    exit();
}

// ============================================
// VERIFICAR ESTADO DEL USUARIO
// ============================================

if ($user['estado'] !== 'activo') {
    $stmt->close();
    $conn->close();
    header("Location: ../login.html?error=" . urlencode("Tu cuenta esta inactiva. Contacta al administrador"));
    exit();
}

// ============================================
// CREAR SESION
// ============================================

// Regenerar ID de sesion para seguridad
session_regenerate_id(true);

// Guardar datos en la sesion
$_SESSION['id'] = $user['id'];
$_SESSION['nombre'] = $user['nombre'];
$_SESSION['apellido'] = $user['apellido'];
$_SESSION['email'] = $user['email'];
$_SESSION['username'] = $user['username'];
$_SESSION['rol'] = $user['rol'];
$_SESSION['genero'] = $user['genero'] ?? 'O';
$_SESSION['login_time'] = time();
$_SESSION['loggedin'] = true;

// ============================================
// ACTUALIZAR ULTIMO ACCESO
// ============================================

$update_sql = "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?";
$update_stmt = $conn->prepare($update_sql);
if ($update_stmt) {
    $update_stmt->bind_param("i", $user['id']);
    $update_stmt->execute();
    $update_stmt->close();
}

// ============================================
// REDIRECCION SEGUN ROL
// ============================================

$stmt->close();
$conn->close();

if ($user['rol'] === 'administrador') {
    header("Location: ../panelAdmin.php");
} else {
    header("Location: ../dashboardCliente.php");
}
exit();
?>
