<?php
/**
 * Clase para enviar correos electronicos
 * Hotel EcoAventura
 */

require_once 'config_smtp.php';

// Incluir PHPMailer (descargalo de https://github.com/PHPMailer/PHPMailer)
// Opcion 1: Si usas Composer
// require_once __DIR__ . '/../vendor/autoload.php';

// Opcion 2: Si descargas manualmente PHPMailer
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

/**
 * Envia un correo electronico usando SMTP
 * 
 * @param string $destinatario Email del destinatario
 * @param string $asunto Asunto del correo
 * @param string $mensaje Cuerpo del mensaje (puede ser HTML)
 * @param string $nombreDestinatario Nombre del destinatario (opcional)
 * @return array ['success' => bool, 'message' => string]
 */
function enviarCorreo($destinatario, $asunto, $mensaje, $nombreDestinatario = '') {
    $mail = new PHPMailer(true);
    
    try {
        // Configuracion del servidor
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';
        
        // Remitente
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        
        // Destinatario
        $mail->addAddress($destinatario, $nombreDestinatario);
        
        // Contenido
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = $mensaje;
        $mail->AltBody = strip_tags($mensaje); // Version texto plano
        
        $mail->send();
        
        return [
            'success' => true,
            'message' => 'Correo enviado exitosamente'
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => "Error al enviar correo: {$mail->ErrorInfo}"
        ];
    }
}

/**
 * Envia el codigo de recuperacion de contrasena
 * 
 * @param string $email Email del usuario
 * @param string $codigo Codigo de 6 digitos
 * @param string $nombre Nombre del usuario
 * @return array
 */
function enviarCodigoRecuperacion($email, $codigo, $nombre = 'Usuario') {
    $asunto = 'Codigo de Recuperacion - Hotel EcoAventura';
    
    $mensaje = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
            .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .header { background: linear-gradient(135deg, #2e7d32, #4caf50); padding: 30px; text-align: center; }
            .header h1 { color: white; margin: 0; font-size: 24px; }
            .header img { width: 80px; height: 80px; margin-bottom: 10px; }
            .content { padding: 30px; }
            .codigo-box { background-color: #e8f5e9; border: 2px dashed #2e7d32; border-radius: 10px; padding: 20px; text-align: center; margin: 20px 0; }
            .codigo { font-size: 36px; font-weight: bold; color: #2e7d32; letter-spacing: 8px; }
            .warning { background-color: #fff3e0; border-left: 4px solid #ff9800; padding: 15px; margin: 20px 0; font-size: 14px; }
            .footer { background-color: #f5f5f5; padding: 20px; text-align: center; font-size: 12px; color: #666; }
            .btn { display: inline-block; background-color: #2e7d32; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin-top: 15px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Hotel EcoAventura</h1>
                <p style="color: #e8f5e9; margin: 5px 0;">Una experiencia ecologica</p>
            </div>
            <div class="content">
                <h2 style="color: #333;">Hola, ' . htmlspecialchars($nombre) . '</h2>
                <p>Recibimos una solicitud para restablecer la contrasena de tu cuenta en Hotel EcoAventura.</p>
                <p>Tu codigo de verificacion es:</p>
                
                <div class="codigo-box">
                    <div class="codigo">' . $codigo . '</div>
                    <p style="margin: 10px 0 0 0; color: #666; font-size: 14px;">Valido por 15 minutos</p>
                </div>
                
                <div class="warning">
                    <strong>Importante:</strong> Si no solicitaste este cambio, ignora este correo. Tu cuenta permanecera segura.
                </div>
                
                <p>Si tienes problemas, contacta a nuestro equipo de soporte.</p>
            </div>
            <div class="footer">
                <p>&copy; ' . date('Y') . ' Hotel EcoAventura. Todos los derechos reservados.</p>
                <p>Este es un correo automatico, por favor no responda a este mensaje.</p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    return enviarCorreo($email, $asunto, $mensaje, $nombre);
}

/**
 * Envia confirmacion de cambio de contrasena
 */
function enviarConfirmacionCambioPassword($email, $nombre = 'Usuario') {
    $asunto = 'Contrasena Actualizada - Hotel EcoAventura';
    
    $mensaje = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
            .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 10px; overflow: hidden; }
            .header { background: linear-gradient(135deg, #2e7d32, #4caf50); padding: 30px; text-align: center; }
            .header h1 { color: white; margin: 0; }
            .content { padding: 30px; }
            .success-icon { font-size: 60px; text-align: center; }
            .footer { background-color: #f5f5f5; padding: 20px; text-align: center; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Hotel EcoAventura</h1>
            </div>
            <div class="content">
                <div class="success-icon">&#9989;</div>
                <h2 style="text-align: center; color: #2e7d32;">Contrasena Actualizada</h2>
                <p>Hola ' . htmlspecialchars($nombre) . ',</p>
                <p>Tu contrasena ha sido actualizada exitosamente. Ya puedes iniciar sesion con tu nueva contrasena.</p>
                <p>Si no realizaste este cambio, contacta inmediatamente a nuestro equipo de soporte.</p>
            </div>
            <div class="footer">
                <p>&copy; ' . date('Y') . ' Hotel EcoAventura</p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    return enviarCorreo($email, $asunto, $mensaje, $nombre);
}
?>
