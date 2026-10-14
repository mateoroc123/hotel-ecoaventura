<?php
// ============================================
// FUNCIONES DE VALIDACIÓN Y SANITIZACIÓN
// Hotel EcoAventura
// ============================================

/**
 * Sanitiza un string de entrada para prevenir XSS
 * @param string $input - Texto a sanitizar
 * @return string - Texto sanitizado
 */
function sanitizar($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Valida un email
 * @param string $email - Email a validar
 * @return bool - true si es válido, false si no
 */
function validarEmail($email) {
    $email = trim($email);
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valida que la contrasena no este vacia
 * @param string $password - Contrasena a validar
 * @return bool|string - true si es valida, string con mensaje de error si no
 */
function validarPassword($password) {
    if (empty($password)) {
        return "La contrasena es requerida";
    }
    // Sin limite de longitud - el usuario puede usar cualquier contrasena
    return true;
}

/**
 * Valida el nombre (solo letras y espacios)
 * @param string $nombre - Nombre a validar
 * @return bool|string - Nombre sanitizado si es válido, string con error si no
 */
function validarNombre($nombre) {
    $nombre = sanitizar($nombre);
    if (empty($nombre)) {
        return "El nombre es requerido";
    }
    if (strlen($nombre) < 2) {
        return "El nombre debe tener al menos 2 caracteres";
    }
    return $nombre;
}

/**
 * Valida el teléfono (números, espacios, guiones, paréntesis, +)
 * @param string $telefono - Teléfono a validar
 * @return bool|string - true si es válido (o vacío), string con error si no
 */
function validarTelefono($telefono) {
    $telefono = sanitizar($telefono);
    if (!empty($telefono) && !preg_match('/^[0-9\s\-\+\(\)]+$/', $telefono)) {
        return "El teléfono contiene caracteres inválidos";
    }
    return true;
}

/**
 * Valida el username (letras, números, guion bajo)
 * @param string $username - Username a validar
 * @return bool|string - true si es válido, string con error si no
 */
function validarUsername($username) {
    $username = trim($username);
    if (empty($username)) {
        return "El nombre de usuario es requerido";
    }
    if (strlen($username) < 4) {
        return "El nombre de usuario debe tener al menos 4 caracteres";
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        return "El nombre de usuario solo puede contener letras, números y guion bajo";
    }
    return true;
}

/**
 * Crea un hash seguro de la contraseña
 * @param string $password - Contraseña en texto plano
 * @return string - Contraseña hasheada
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verifica una contraseña contra su hash
 * @param string $password - Contraseña en texto plano
 * @param string $hash - Hash almacenado
 * @return bool - true si coincide, false si no
 */
function verificarPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Genera respuesta JSON estándar para APIs
 * @param bool $exito - true si la operación fue exitosa
 * @param string $mensaje - Mensaje de respuesta
 * @param array|null $datos - Datos adicionales opcionales
 */
function respuesta($exito, $mensaje, $datos = null) {
    header('Content-Type: application/json; charset=utf-8');
    $response = [
        'exito' => $exito,
        'mensaje' => $mensaje
    ];
    if ($datos !== null) {
        $response['datos'] = $datos;
    }
    echo json_encode($response);
    exit;
}

/**
 * Verifica si el usuario está logueado
 * @return bool - true si está logueado, false si no
 */
function estaLogueado() {
    return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
}

/**
 * Verifica si el usuario es administrador
 * @return bool - true si es admin, false si no
 */
function esAdmin() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador';
}

/**
 * Redirige si no está logueado
 */
function requireLogin() {
    if (!estaLogueado()) {
        header("Location: login.html?error=" . urlencode("Debes iniciar sesión"));
        exit();
    }
}

/**
 * Redirige si no es administrador
 */
function requireAdmin() {
    requireLogin();
    if (!esAdmin()) {
        header("Location: index.html?error=" . urlencode("Acceso denegado"));
        exit();
    }
}
?>
