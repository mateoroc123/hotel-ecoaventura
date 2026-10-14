<?php
/**
 * Configuracion SMTP para envio de correos
 * Hotel EcoAventura
 * 
 * INSTRUCCIONES:
 * 1. Descarga PHPMailer desde: https://github.com/PHPMailer/PHPMailer
 * 2. Extrae los archivos en la carpeta Php/PHPMailer/
 * 3. Configura las credenciales de tu proveedor de correo abajo
 */

// Configuracion del servidor SMTP
define('SMTP_HOST', 'smtp.gmail.com');      // Servidor SMTP (Gmail, Outlook, etc.)
define('SMTP_PORT', 587);                    // Puerto (587 para TLS, 465 para SSL)
define('SMTP_USERNAME', 'tu_correo@gmail.com'); // Tu correo electronico
define('SMTP_PASSWORD', 'tu_contrasena_app');   // Contrasena de aplicacion (no la normal)
define('SMTP_FROM_EMAIL', 'tu_correo@gmail.com'); // Email del remitente
define('SMTP_FROM_NAME', 'Hotel EcoAventura');    // Nombre del remitente
define('SMTP_ENCRYPTION', 'tls');            // 'tls' o 'ssl'

/**
 * CONFIGURACION PARA DIFERENTES PROVEEDORES:
 * 
 * ============= GMAIL =============
 * Host: smtp.gmail.com
 * Puerto: 587 (TLS) o 465 (SSL)
 * 
 * IMPORTANTE PARA GMAIL:
 * 1. Activa la verificacion en 2 pasos en tu cuenta Google
 * 2. Ve a: https://myaccount.google.com/apppasswords
 * 3. Genera una "Contrasena de aplicacion" para "Correo"
 * 4. Usa esa contrasena de 16 caracteres en SMTP_PASSWORD
 * 
 * ============= OUTLOOK/HOTMAIL =============
 * Host: smtp.office365.com
 * Puerto: 587
 * Encryption: tls
 * 
 * ============= YAHOO =============
 * Host: smtp.mail.yahoo.com
 * Puerto: 587 o 465
 * 
 * ============= HOSTINGER =============
 * Host: smtp.hostinger.com
 * Puerto: 587
 * 
 * ============= cPanel/Hosting compartido =============
 * Host: mail.tudominio.com
 * Puerto: 587 o 465
 * Usuario: tu_correo@tudominio.com
 * 
 */
?>
