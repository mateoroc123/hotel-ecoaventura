<?php
require('session.php');
requerirAdmin();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>User Admin</title>
</head>
<body>
    <section>
        <article>
            <h1>Administración de Usuarios</h1>
            <section>
                <article>
                    <p><a href="/registros/registrousuarios/regUser.html">Crear Usuarios</a></p>    
                </article>
                <article>
                    <p><a href="/registros/registrousuarios/Php/listar.php">Listar Usuarios</a></p>
                </article>
                <article>
                    <p>Eliminar Usuarios (Próximamente)</p>
                </article>
                <article>
                    <p>Actualizar Usuarios (Próximamente)</p>
                </article>
            </section>
            <br>
            <p><a href="/registros/registrousuarios/login.html">Cerrar Sesión</a></p>
        </article>
    </section>
</body>
</html>