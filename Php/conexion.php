<?php
// ============================================
// CONEXIÓN A LA BASE DE DATOS
// Hotel EcoAventura - XAMPP/MySQL
// ============================================

// Configuración de la base de datos
$host = "localhost";      // Servidor (localhost para XAMPP)
$user = "root";           // Usuario de MySQL (por defecto "root" en XAMPP)
$password = "";           // Contraseña (vacía por defecto en XAMPP)
$database = "hotel";      // Nombre de la base de datos

// Crear conexión usando mysqli
$conn = new mysqli($host, $user, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

// Establecer charset UTF-8 para soportar caracteres especiales
$conn->set_charset("utf8mb4");

// Zona horaria de Colombia
date_default_timezone_set('America/Bogota');
?>
