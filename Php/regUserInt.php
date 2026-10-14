<?php
// ============================================
// REGISTRO USUARIO INTERNO (Admin) - Hotel EcoAventura
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
$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$tipo_documento = $_POST['tipo_documento'] ?? 'CC';
$numero_documento = trim($_POST['numero_documento'] ?? '');
$genero = $_POST['genero'] ?? 'M';
$email = trim($_POST['email'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$rol = $_POST['rol'] ?? 'cliente';
$telefono = trim($_POST['telefono'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');

// Validaciones basicas
if (empty($nombre) || empty($apellido) || empty($numero_documento) || empty($email) || empty($username) || empty($password)) {
    header("Location: ../panelAdmin.php?error=" . urlencode("Todos los campos obligatorios deben ser completados"));
    exit();
}

// Validar formato de email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../panelAdmin.php?error=" . urlencode("Email no valido"));
    exit();
}

// Validar longitud de contrasena
if (strlen($password) < 6) {
    header("Location: ../panelAdmin.php?error=" . urlencode("La contrasena debe tener al menos 6 caracteres"));
    exit();
}

// Verificar duplicados - email
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    header("Location: ../panelAdmin.php?error=" . urlencode("El email ya esta registrado"));
    exit();
}
$stmt->close();

// Verificar duplicados - username
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    header("Location: ../panelAdmin.php?error=" . urlencode("El nombre de usuario ya existe"));
    exit();
}
$stmt->close();

// Verificar duplicados - documento
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE tipo_documento = ? AND numero_documento = ?");
$stmt->bind_param("ss", $tipo_documento, $numero_documento);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    header("Location: ../panelAdmin.php?error=" . urlencode("El documento ya esta registrado"));
    exit();
}
$stmt->close();

// Encriptar contrasena
$password_hash = password_hash($password, PASSWORD_BCRYPT);

// Validar valores de enum
$tipos_validos = ['CC', 'CE', 'TI', 'PP'];
$generos_validos = ['M', 'F', 'O'];
$roles_validos = ['cliente', 'administrador'];

if (!in_array($tipo_documento, $tipos_validos)) $tipo_documento = 'CC';
if (!in_array($genero, $generos_validos)) $genero = 'M';
if (!in_array($rol, $roles_validos)) $rol = 'cliente';

// Insertar usuario
$sql = "INSERT INTO usuarios (nombre, apellido, tipo_documento, numero_documento, email, username, password, telefono, direccion, genero, rol, estado) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'activo')";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssssss", $nombre, $apellido, $tipo_documento, $numero_documento, $email, $username, $password_hash, $telefono, $direccion, $genero, $rol);

if ($stmt->execute()) {
    header("Location: ../panelAdmin.php?success=" . urlencode("Usuario creado correctamente"));
} else {
    header("Location: ../panelAdmin.php?error=" . urlencode("Error al crear usuario: " . $conn->error));
}

$stmt->close();
$conn->close();
exit();
?>
