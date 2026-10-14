<?php
// ============================================
// VERIFICAR SESION - Hotel EcoAventura
// Devuelve info del usuario si esta logueado
// ============================================

session_start();
header('Content-Type: application/json');

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    echo json_encode([
        'loggedin' => true,
        'user' => [
            'id' => $_SESSION['id'],
            'nombre' => $_SESSION['nombre'],
            'apellido' => $_SESSION['apellido'],
            'email' => $_SESSION['email'],
            'rol' => $_SESSION['rol']
        ]
    ]);
} else {
    echo json_encode(['loggedin' => false]);
}
?>
