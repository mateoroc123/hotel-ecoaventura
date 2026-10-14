<?php
require 'Php/session.php';
requerirAdmin();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrativo | Hotel EcoAventura</title>
</head>
<body>
    <section>
        <article>
            <h1>Panel Administrativo - Hotel EcoAventura</h1>
            <p>Bienvenido al módulo de administración del hotel.</p>

            <section>
                <article>
                    <p><a href="regUser.html">Registrar Usuarios</a></p>
                </article>

                <article>
                    <p><a href="Php/listar.php">Listar Usuarios</a></p>
                </article>

                <article>
                    <p><a href="Php/habitaciones/listarHabitacion.php">Gestionar Habitaciones</a></p>
                </article>

                <article>
                    <p><a href="Php/reservas/listarReserva.php">Gestionar Reservas</a></p>
                </article>

                <article>
                    <p><a href="Php/eliminar.php">Eliminar Usuarios</a></p>
                </article>

                <article>
                    <p><a href="Php/actualizar.php">Actualizar Usuarios</a></p>
                </article>
            </section>

            <br>

            <p><a href="Php/logout.php">Cerrar Sesión</a></p>
        </article>
    </section>
</body>
</html>