<?php
// ============================================
// LOGIN AJAX - Hotel EcoAventura
// Permite iniciar sesion sin redireccion
// ============================================

session_start();
header('Content-Type: application/json');
include("conexion.php");

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metodo no permitido']);
    exit();
}

// Recibir datos
$usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Validar campos vacios
if (empty($usuario) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Usuario y contrasena son requeridos']);
    exit();
}

// Buscar usuario por email o username
$sql = "SELECT id, nombre, apellido, email, username, password, rol, estado, genero 
        FROM usuarios 
        WHERE email = ? OR username = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error de base de datos']);
    exit();
}

$stmt->bind_param("ss", $usuario, $usuario);
$stmt->execute();
$result = $stmt->get_result();

// Verificar si existe el usuario
if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Usuario o contrasena incorrectos']);
    exit();
}

$user = $result->fetch_assoc();

// Verificar contrasena
if (!password_verify($password, $user['password'])) {
    $stmt->close();
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Usuario o contrasena incorrectos']);
    exit();
}

// Verificar estado
if ($user['estado'] !== 'activo') {
    $stmt->close();
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Tu cuenta esta inactiva. Contacta al administrador']);
    exit();
}

// Crear sesion
session_regenerate_id(true);
$_SESSION['id'] = $user['id'];
$_SESSION['nombre'] = $user['nombre'];
$_SESSION['apellido'] = $user['apellido'];
$_SESSION['email'] = $user['email'];
$_SESSION['username'] = $user['username'];
$_SESSION['rol'] = $user['rol'];
$_SESSION['genero'] = $user['genero'] ?? 'O';
$_SESSION['login_time'] = time();
$_SESSION['loggedin'] = true;

// Actualizar ultimo acceso
$update_sql = "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?";
$update_stmt = $conn->prepare($update_sql);
if ($update_stmt) {
    $update_stmt->bind_param("i", $user['id']);
    $update_stmt->execute();
    $update_stmt->close();
}

$stmt->close();
$conn->close();

// Respuesta exitosa
$genero_user = $user['genero'] ?? 'O';
$saludo = ($genero_user === 'F') ? 'Bienvenida' : 'Bienvenido';

echo json_encode([
    'success' => true,
    'message' => 'Inicio de sesion exitoso',
    'saludo' => $saludo,
    'user' => [
        'id' => $user['id'],
        'nombre' => $user['nombre'],
        'apellido' => $user['apellido'],
        'email' => $user['email'],
        'rol' => $user['rol'],
        'genero' => $genero_user
    ],
    'redirect' => $user['rol'] === 'administrador' ? 'panelAdmin.php' : 'dashboardCliente.php'
]);
?>
