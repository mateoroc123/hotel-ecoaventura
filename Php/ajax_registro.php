<?php
// ============================================
// REGISTRO AJAX - Hotel EcoAventura
// Permite registrarse sin redireccion
// ============================================

header('Content-Type: application/json');
include("conexion.php");

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metodo no permitido']);
    exit();
}

// Recibir datos del formulario simple del modal
$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$edad = trim($_POST['edad'] ?? '');
$genero = trim($_POST['genero'] ?? 'O'); // M = Masculino, F = Femenino, O = Otro/Prefiero no decir

// Validaciones basicas
if (empty($nombre) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
    exit();
}

// Validar formato de email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'El correo electronico no es valido']);
    exit();
}

// Verificar si el email ya existe
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'El correo electronico ya esta registrado']);
    exit();
}
$stmt->close();

// Generar username desde el nombre
$username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $nombre)) . rand(100, 999);

// Verificar que el username no exista
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
while ($result->num_rows > 0) {
    $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $nombre)) . rand(100, 999);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
}
$stmt->close();

// Encriptar contrasena
$password_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

// Separar nombre y apellido si es posible
$partes_nombre = explode(' ', $nombre, 2);
$primer_nombre = $partes_nombre[0];
$apellido = isset($partes_nombre[1]) ? $partes_nombre[1] : '';

// Validar genero
if (!in_array($genero, ['M', 'F', 'O'])) {
    $genero = 'O';
}

// Insertar usuario con valores por defecto
$sql = "INSERT INTO usuarios (nombre, apellido, tipo_documento, numero_documento, email, username, password, telefono, direccion, genero, rol, estado) 
        VALUES (?, ?, 'CC', ?, ?, ?, ?, '', '', ?, 'cliente', 'activo')";

$numero_doc = 'TEMP' . time();

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssss", 
    $primer_nombre, 
    $apellido, 
    $numero_doc, 
    $email, 
    $username, 
    $password_hash,
    $genero
);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true, 
        'message' => 'Registro exitoso. Ahora puedes iniciar sesion.',
        'username' => $username
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al registrar: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>
