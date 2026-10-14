<?php
// ============================================
// REGISTRO DE USUARIO - Hotel EcoAventura
// ============================================

// Incluir conexion a la base de datos
include("conexion.php");

// Verificar que sea metodo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../regUser.html?error=" . urlencode("Acceso no permitido"));
    exit();
}

// Recibir y sanitizar datos del formulario
$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$tipo_documento = trim($_POST['tipo_documento'] ?? '');
$numero_documento = trim($_POST['numero_documento'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirmar_password = $_POST['confirmar_password'] ?? '';
$telefono = trim($_POST['telefono'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');
$genero = trim($_POST['genero'] ?? '');
$rol = trim($_POST['rol'] ?? 'cliente');

// ============================================
// VALIDACIONES
// ============================================

// Campos obligatorios
if (empty($nombre) || empty($apellido) || empty($tipo_documento) || 
    empty($numero_documento) || empty($username) || empty($email) || 
    empty($password) || empty($genero)) {
    header("Location: ../regUser.html?error=" . urlencode("Todos los campos obligatorios deben ser completados"));
    exit();
}

// Validar que las contrasenas coincidan
if ($password !== $confirmar_password) {
    header("Location: ../regUser.html?error=" . urlencode("Las contrasenas no coinciden"));
    exit();
}

// Validar que la contrasena no este vacia
if (empty($password)) {
    header("Location: ../regUser.html?error=" . urlencode("La contrasena es requerida"));
    exit();
}

// Validar formato de email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../regUser.html?error=" . urlencode("El correo electronico no es valido"));
    exit();
}

// Validar username (solo letras, numeros y guion bajo)
if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    header("Location: ../regUser.html?error=" . urlencode("El nombre de usuario solo puede contener letras, numeros y guion bajo"));
    exit();
}

// Validar tipo de documento
$tipos_validos = ['CC', 'CE', 'TI', 'PP'];
if (!in_array($tipo_documento, $tipos_validos)) {
    header("Location: ../regUser.html?error=" . urlencode("Tipo de documento no valido"));
    exit();
}

// Validar genero
$generos_validos = ['M', 'F', 'O'];
if (!in_array($genero, $generos_validos)) {
    header("Location: ../regUser.html?error=" . urlencode("Genero no valido"));
    exit();
}

// Validar rol
$roles_validos = ['cliente', 'administrador'];
if (!in_array($rol, $roles_validos)) {
    $rol = 'cliente'; // Por defecto cliente si no es valido
}

// ============================================
// VERIFICAR DUPLICADOS
// ============================================

// Verificar si el email ya existe
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    header("Location: ../regUser.html?error=" . urlencode("El correo electronico ya esta registrado"));
    exit();
}
$stmt->close();

// Verificar si el username ya existe
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    header("Location: ../regUser.html?error=" . urlencode("El nombre de usuario ya esta en uso"));
    exit();
}
$stmt->close();

// Verificar si el documento ya existe
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE tipo_documento = ? AND numero_documento = ?");
$stmt->bind_param("ss", $tipo_documento, $numero_documento);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    header("Location: ../regUser.html?error=" . urlencode("El documento ya esta registrado"));
    exit();
}
$stmt->close();

// ============================================
// INSERTAR USUARIO
// ============================================

// Encriptar contrasena con bcrypt (cost 10 para compatibilidad)
$password_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

// Preparar consulta INSERT
$sql = "INSERT INTO usuarios (nombre, apellido, tipo_documento, numero_documento, email, username, password, telefono, direccion, genero, rol, estado) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'activo')";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssssss", 
    $nombre, 
    $apellido, 
    $tipo_documento, 
    $numero_documento, 
    $email, 
    $username, 
    $password_hash, 
    $telefono, 
    $direccion, 
    $genero,
    $rol
);

if ($stmt->execute()) {
    // Registro exitoso - redirigir al login
    header("Location: ../login.html?registro=exitoso");
    exit();
} else {
    // Error al insertar
    header("Location: ../regUser.html?error=" . urlencode("Error al registrar: " . $conn->error));
    exit();
}

$stmt->close();
$conn->close();
?>
