<?php
session_start();

function requerirSesion() {
    if (!isset($_SESSION['usuario'])) {
        header("Location: ../login.html");
        exit();
    }
}

function requerirAdmin() {
    requerirSesion();

    if ($_SESSION['rol'] !== 'Admi') {
        echo "Acceso denegado. Solo administradores pueden ingresar.";
        exit();
    }
}
?>