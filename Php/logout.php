<?php
/*
|--------------------------------------------------------------------------
| Cerrar sesión - Hotel EcoAventura
|--------------------------------------------------------------------------
| Destruye la sesión activa del usuario y lo redirige
| al login principal del hotel.
*/

session_start();

/* Vaciar variables de sesión */
$_SESSION = [];

/* Destruir sesión */
session_destroy();

/* Redirigir al login */
header("Location: ../login.html");
exit();
?>