<?php
// ============================================
// RECUPERAR PASSWORD - Hotel EcoAventura
// Genera codigo y lo guarda para verificacion
// ============================================

session_start();
header('Content-Type: application/json');
include("conexion.php");

// Incluir sistema de correo (comentar si no esta configurado)
$smtp_configurado = false; // Cambiar a true cuando configures SMTP
if ($smtp_configurado && file_exists(__DIR__ . '/enviar_correo.php')) {
    include("enviar_correo.php");
}

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metodo no permitido']);
    exit();
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'solicitar_codigo':
        // Solicitar codigo de recuperacion
        $email = trim($_POST['email'] ?? '');
        
        if (empty($email)) {
            echo json_encode(['success' => false, 'message' => 'El correo es requerido']);
            exit();
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Correo no valido']);
            exit();
        }
        
        // Verificar si el email existe
        $stmt = $conn->prepare("SELECT id, nombre FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'No existe una cuenta con ese correo']);
            exit();
        }
        
        $user = $result->fetch_assoc();
        $stmt->close();
        
        // Generar codigo de 6 digitos
        $codigo = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expira = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        
        // Verificar si existe la columna de codigo
        $check_column = $conn->query("SHOW COLUMNS FROM usuarios LIKE 'codigo_recuperacion'");
        if ($check_column->num_rows === 0) {
            // Crear columnas si no existen
            $conn->query("ALTER TABLE usuarios ADD COLUMN codigo_recuperacion VARCHAR(6) NULL");
            $conn->query("ALTER TABLE usuarios ADD COLUMN codigo_expira DATETIME NULL");
        }
        
        // Guardar codigo en la base de datos
        $stmt = $conn->prepare("UPDATE usuarios SET codigo_recuperacion = ?, codigo_expira = ? WHERE email = ?");
        $stmt->bind_param("sss", $codigo, $expira, $email);
        
        if ($stmt->execute()) {
            // Guardar email en sesion para verificacion
            $_SESSION['recovery_email'] = $email;
            
            // Enviar correo con el codigo
            global $smtp_configurado;
            $email_enviado = false;
            
            if ($smtp_configurado && function_exists('enviarCodigoRecuperacion')) {
                $resultado_email = enviarCodigoRecuperacion($email, $codigo, $user['nombre']);
                $email_enviado = $resultado_email['success'];
            }
            
            // Respuesta
            $response = [
                'success' => true, 
                'message' => 'Se ha enviado un codigo de verificacion a tu correo',
                'nombre' => $user['nombre']
            ];
            
            // Si SMTP no esta configurado, mostrar codigo para pruebas
            if (!$smtp_configurado || !$email_enviado) {
                $response['codigo_debug'] = $codigo;
                $response['message'] = 'Codigo generado (modo prueba - SMTP no configurado)';
            }
            
            echo json_encode($response);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al generar codigo']);
        }
        $stmt->close();
        break;
        
    case 'verificar_codigo':
        // Verificar codigo ingresado
        $email = $_SESSION['recovery_email'] ?? ($_POST['email'] ?? '');
        $codigo = trim($_POST['codigo'] ?? '');
        
        if (empty($email) || empty($codigo)) {
            echo json_encode(['success' => false, 'message' => 'Codigo requerido']);
            exit();
        }
        
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ? AND codigo_recuperacion = ? AND codigo_expira > NOW()");
        $stmt->bind_param("ss", $email, $codigo);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Codigo invalido o expirado']);
            exit();
        }
        
        $_SESSION['recovery_verified'] = true;
        $_SESSION['recovery_user_id'] = $result->fetch_assoc()['id'];
        
        echo json_encode(['success' => true, 'message' => 'Codigo verificado correctamente']);
        $stmt->close();
        break;
        
    case 'cambiar_password':
        // Cambiar la contrasena
        if (!isset($_SESSION['recovery_verified']) || $_SESSION['recovery_verified'] !== true) {
            echo json_encode(['success' => false, 'message' => 'Sesion no verificada']);
            exit();
        }
        
        $nueva_password = $_POST['nueva_password'] ?? '';
        $confirmar_password = $_POST['confirmar_password'] ?? '';
        
        if (empty($nueva_password)) {
            echo json_encode(['success' => false, 'message' => 'La nueva contrasena es requerida']);
            exit();
        }
        
        if ($nueva_password !== $confirmar_password) {
            echo json_encode(['success' => false, 'message' => 'Las contrasenas no coinciden']);
            exit();
        }
        
        if (strlen($nueva_password) < 4) {
            echo json_encode(['success' => false, 'message' => 'La contrasena debe tener al menos 4 caracteres']);
            exit();
        }
        
        $user_id = $_SESSION['recovery_user_id'];
        $password_hash = password_hash($nueva_password, PASSWORD_BCRYPT, ['cost' => 10]);
        
        // Actualizar contrasena y limpiar codigo
        $stmt = $conn->prepare("UPDATE usuarios SET password = ?, codigo_recuperacion = NULL, codigo_expira = NULL WHERE id = ?");
        $stmt->bind_param("si", $password_hash, $user_id);
        
        if ($stmt->execute()) {
            // Enviar email de confirmacion si SMTP esta configurado
            global $smtp_configurado;
            $email_recovery = $_SESSION['recovery_email'] ?? '';
            
            if ($smtp_configurado && function_exists('enviarConfirmacionCambioPassword') && !empty($email_recovery)) {
                enviarConfirmacionCambioPassword($email_recovery);
            }
            
            // Limpiar sesion de recuperacion
            unset($_SESSION['recovery_email']);
            unset($_SESSION['recovery_verified']);
            unset($_SESSION['recovery_user_id']);
            
            echo json_encode(['success' => true, 'message' => 'Contrasena actualizada exitosamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar contrasena']);
        }
        $stmt->close();
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Accion no valida']);
}

$conn->close();
?>
