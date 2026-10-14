<?php
require 'conexion.php';
require 'session.php';

/*
|--------------------------------------------------------------------------
| Validar acceso administrador
|--------------------------------------------------------------------------
*/
requerirAdmin();

/*
|--------------------------------------------------------------------------
| Consultar usuarios registrados
|--------------------------------------------------------------------------
*/
$sql = "SELECT * FROM usuarios";
$resultado = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Usuarios | Hotel EcoAventura</title>
</head>
<body>

    <h2>Usuarios Registrados - Hotel EcoAventura</h2>
    <p>Panel de administración de clientes y personal del hotel.</p>

    <table border="1" cellpadding="8">
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Rol</th>
            <th>Acciones</th>
        </tr>

        <?php while($row = mysqli_fetch_assoc($resultado)): ?>
        <tr>
            <td><?php echo $row['idUser']; ?></td>
            <td><?php echo $row['nameUser']; ?></td>
            <td><?php echo $row['emailUser']; ?></td>
            <td><?php echo $row['typeUser']; ?></td>
            <td>
                <a href="actualizar.php?id=<?php echo $row['idUser']; ?>">Actualizar</a> |
                <a href="eliminar.php?id=<?php echo $row['idUser']; ?>">Eliminar</a>
            </td>
        </tr>
        <?php endwhile; ?>

    </table>

    <br>
    <a href="../panelAdmin.php">Volver al Panel Administrativo</a>

</body>
</html>