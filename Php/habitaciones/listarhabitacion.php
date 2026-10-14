<?php
require '../conexion.php';
require '../session.php';

requerirAdmin();

$sql = "SELECT * FROM habitaciones";
$resultado = mysqli_query($conn, $sql);
?>

<h2>Lista de Habitaciones - Hotel EcoAventura</h2>

<table border="1">
    <tr>
        <th>ID</th>
        <th>Número</th>
        <th>Tipo</th>
        <th>Precio</th>
        <th>Estado</th>
        <th>Acciones</th>
    </tr>

    <?php while($row = mysqli_fetch_assoc($resultado)): ?>
    <tr>
        <td><?php echo $row['idHabitacion']; ?></td>
        <td><?php echo $row['numeroHabitacion']; ?></td>
        <td><?php echo $row['tipoHabitacion']; ?></td>
        <td><?php echo $row['precio']; ?></td>
        <td><?php echo $row['estado']; ?></td>
        <td>
            <a href="actualizarHabitacion.php?id=<?php echo $row['idHabitacion']; ?>">Editar</a> |
            <a href="eliminarHabitacion.php?id=<?php echo $row['idHabitacion']; ?>">Eliminar</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>