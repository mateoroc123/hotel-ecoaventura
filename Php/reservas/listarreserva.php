<?php
require '../conexion.php';
require '../session.php';

requerirAdmin();

$sql = "SELECT * FROM reservas";
$resultado = mysqli_query($conn, $sql);
?>

<h2>Lista de Reservas</h2>

<table border="1">
    <tr>
        <th>ID</th>
        <th>Usuario</th>
        <th>Habitación</th>
        <th>Entrada</th>
        <th>Salida</th>
        <th>Acciones</th>
    </tr>

    <?php while($row = mysqli_fetch_assoc($resultado)): ?>
    <tr>
        <td><?php echo $row['idReserva']; ?></td>
        <td><?php echo $row['idUser']; ?></td>
        <td><?php echo $row['idHabitacion']; ?></td>
        <td><?php echo $row['fechaEntrada']; ?></td>
        <td><?php echo $row['fechaSalida']; ?></td>
        <td>
            <a href="cancelarReserva.php?id=<?php echo $row['idReserva']; ?>">Cancelar</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>