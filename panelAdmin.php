<?php
// ============================================
// PANEL ADMINISTRADOR - Hotel EcoAventura
// ============================================
session_start();

// Verificar si esta logueado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.html?error=" . urlencode("Debes iniciar sesion"));
    exit();
}

// Verificar que sea administrador
if ($_SESSION['rol'] !== 'administrador') {
    header("Location: dashboardCliente.php");
    exit();
}

// Incluir conexion
include("Php/conexion.php");

// Obtener datos del admin
$admin_nombre = $_SESSION['nombre'];
$admin_apellido = $_SESSION['apellido'];

// Estadisticas generales
$stats = [];

// Total usuarios
$result = $conn->query("SELECT COUNT(*) as total FROM usuarios");
$stats['usuarios'] = $result->fetch_assoc()['total'];

// Total habitaciones
$result = $conn->query("SELECT COUNT(*) as total FROM habitaciones");
$stats['habitaciones'] = $result->fetch_assoc()['total'];

// Habitaciones disponibles
$result = $conn->query("SELECT COUNT(*) as total FROM habitaciones WHERE estado = 'disponible'");
$stats['disponibles'] = $result->fetch_assoc()['total'];

// Total reservas
$result = $conn->query("SELECT COUNT(*) as total FROM reservas");
$stats['reservas'] = $result->fetch_assoc()['total'];

// Reservas pendientes
$result = $conn->query("SELECT COUNT(*) as total FROM reservas WHERE estado = 'pendiente'");
$stats['pendientes'] = $result->fetch_assoc()['total'];

// Ingresos totales
$result = $conn->query("SELECT COALESCE(SUM(precio_total), 0) as total FROM reservas WHERE estado IN ('confirmada', 'completada')");
$stats['ingresos'] = $result->fetch_assoc()['total'];

// Obtener todos los usuarios
$usuarios = $conn->query("SELECT * FROM usuarios ORDER BY fecha_registro DESC");

// Obtener todas las habitaciones
$habitaciones = $conn->query("SELECT * FROM habitaciones ORDER BY numero_habitacion");

// Obtener todas las reservas con datos relacionados
$reservas = $conn->query("
    SELECT r.*, u.nombre, u.apellido, u.email, h.numero_habitacion, h.tipo 
    FROM reservas r 
    INNER JOIN usuarios u ON r.id_usuario = u.id 
    INNER JOIN habitaciones h ON r.id_habitacion = h.id 
    ORDER BY r.fecha_reserva DESC
");

// Mensajes de exito/error
$mensaje = '';
$tipo_mensaje = '';
if (isset($_GET['success'])) {
    $mensaje = urldecode($_GET['success']);
    $tipo_mensaje = 'success';
} elseif (isset($_GET['error'])) {
    $mensaje = urldecode($_GET['error']);
    $tipo_mensaje = 'danger';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - Hotel EcoAventura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-green: #2d5a27;
            --light-green: #4a7c42;
            --accent-orange: #e67e22;
            --cream: #f5f1e8;
            --dark: #1a1a1a;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 260px;
            background: linear-gradient(180deg, #0f0f23 0%, #1a1a2e 100%);
            padding: 20px;
            z-index: 1000;
            border-right: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-logo {
            text-align: center;
            padding: 15px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 25px;
        }
        
        .sidebar-logo img { width: 80px; height: auto; }
        .sidebar-logo h3 { color: white; margin-top: 10px; font-size: 1rem; }
        .sidebar-logo span { color: var(--accent-orange); font-size: 0.75rem; }
        
        .nav-menu { list-style: none; padding: 0; }
        .nav-item { margin-bottom: 5px; }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 18px;
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover, .nav-link.active {
            background: linear-gradient(90deg, var(--primary-green), var(--light-green));
            color: white;
        }
        
        .nav-link i { width: 22px; margin-right: 10px; font-size: 0.95rem; }
        
        .main-content {
            margin-left: 260px;
            padding: 25px;
            min-height: 100vh;
        }
        
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
            padding: 18px 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        .welcome-text h1 { color: white; font-size: 1.4rem; margin-bottom: 3px; }
        .welcome-text p { color: rgba(255,255,255,0.6); font-size: 0.85rem; }
        
        .admin-badge {
            background: linear-gradient(90deg, var(--accent-orange), #f39c12);
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 18px;
            margin-bottom: 25px;
        }
        
        .stat-card {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.1);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            border-color: var(--primary-green);
        }
        
        .stat-card .stat-icon {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            color: white;
            margin-bottom: 12px;
        }
        
        .stat-card .stat-icon.green { background: linear-gradient(135deg, var(--primary-green), var(--light-green)); }
        .stat-card .stat-icon.orange { background: linear-gradient(135deg, #e67e22, #f39c12); }
        .stat-card .stat-icon.blue { background: linear-gradient(135deg, #3498db, #2980b9); }
        .stat-card .stat-icon.purple { background: linear-gradient(135deg, #9b59b6, #8e44ad); }
        .stat-card .stat-icon.red { background: linear-gradient(135deg, #e74c3c, #c0392b); }
        .stat-card .stat-icon.teal { background: linear-gradient(135deg, #1abc9c, #16a085); }
        
        .stat-card h3 { color: white; font-size: 1.7rem; margin-bottom: 3px; }
        .stat-card p { color: rgba(255,255,255,0.6); font-size: 0.8rem; margin: 0; }
        
        .content-card {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 22px;
            margin-bottom: 25px;
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .card-header h2 {
            color: white;
            font-size: 1.15rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-add {
            background: linear-gradient(90deg, var(--primary-green), var(--light-green));
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
        }
        
        .btn-add:hover { transform: scale(1.03); }
        
        .data-table { width: 100%; border-collapse: collapse; }
        
        .data-table th {
            background: rgba(45, 90, 39, 0.3);
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
        }
        
        .data-table td {
            padding: 12px;
            color: rgba(255,255,255,0.8);
            border-bottom: 1px solid rgba(255,255,255,0.05);
            vertical-align: middle;
            font-size: 0.9rem;
        }
        
        .data-table tr:hover { background: rgba(255,255,255,0.02); }
        
        .status-badge {
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        
        .status-activo, .status-disponible, .status-confirmada, .status-completada { 
            background: rgba(39, 174, 96, 0.2); color: #27ae60; 
        }
        .status-inactivo, .status-ocupada, .status-cancelada { 
            background: rgba(231, 76, 60, 0.2); color: #e74c3c; 
        }
        .status-pendiente { background: rgba(241, 196, 15, 0.2); color: #f1c40f; }
        .status-mantenimiento { background: rgba(52, 152, 219, 0.2); color: #3498db; }
        
        .rol-badge { padding: 3px 8px; border-radius: 12px; font-size: 0.7rem; font-weight: 600; }
        .rol-administrador { background: rgba(230, 126, 34, 0.2); color: #e67e22; }
        .rol-cliente { background: rgba(52, 152, 219, 0.2); color: #3498db; }
        
        .btn-action {
            padding: 5px 10px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 0.75rem;
            margin-right: 4px;
            transition: all 0.3s ease;
        }
        
        .btn-edit { background: rgba(52, 152, 219, 0.2); color: #3498db; }
        .btn-edit:hover { background: #3498db; color: white; }
        .btn-delete { background: rgba(231, 76, 60, 0.2); color: #e74c3c; }
        .btn-delete:hover { background: #e74c3c; color: white; }
        
        .modal-content {
            background: #1a1a2e;
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        .modal-header {
            background: linear-gradient(90deg, var(--primary-green), var(--light-green));
            border-bottom: none;
        }
        
        .modal-header .modal-title { color: white; }
        .modal-header .btn-close { filter: invert(1); }
        .modal-body { padding: 25px; }
        .form-label { color: rgba(255,255,255,0.8); font-weight: 500; font-size: 0.9rem; }
        
        .form-control, .form-select {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            color: white;
            border-radius: 8px;
            padding: 10px 12px;
        }
        
        .form-control:focus, .form-select:focus {
            background: rgba(255,255,255,0.08);
            border-color: var(--primary-green);
            color: white;
            box-shadow: 0 0 0 0.2rem rgba(45, 90, 39, 0.2);
        }
        
        .form-control::placeholder { color: rgba(255,255,255,0.4); }
        .form-select option { background: #1a1a2e; color: white; }
        .modal-footer { border-top: 1px solid rgba(255,255,255,0.1); }
        
        .btn-primary {
            background: linear-gradient(90deg, var(--primary-green), var(--light-green));
            border: none;
            padding: 10px 25px;
            border-radius: 20px;
        }
        
        .btn-secondary {
            background: rgba(255,255,255,0.1);
            border: none;
            color: white;
            padding: 10px 25px;
            border-radius: 20px;
        }
        
        .alert-floating {
            position: fixed;
            top: 15px;
            right: 15px;
            z-index: 9999;
            min-width: 280px;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        .btn-logout {
            background: linear-gradient(90deg, #e74c3c, #c0392b);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }
        
        .btn-logout:hover { transform: scale(1.03); color: white; }
        .table-responsive { overflow-x: auto; }
        
        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); transition: transform 0.3s ease; }
            .sidebar.active { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .toggle-btn { display: block !important; }
        }
        
        .toggle-btn {
            display: none;
            background: var(--primary-green);
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?php if ($mensaje): ?>
    <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-floating alert-dismissible fade show">
        <?php echo htmlspecialchars($mensaje); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <nav class="sidebar">
        <div class="sidebar-logo">
            <img src="img/logo.png" alt="Hotel EcoAventura">
            <h3>Hotel EcoAventura</h3>
            <span>Panel de Administracion</span>
        </div>
        
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="#" class="nav-link active" onclick="showSection('dashboard')">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link" onclick="showSection('usuarios')">
                    <i class="fas fa-users"></i> Usuarios
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link" onclick="showSection('habitaciones')">
                    <i class="fas fa-bed"></i> Habitaciones
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link" onclick="showSection('reservas')">
                    <i class="fas fa-calendar-check"></i> Reservas
                </a>
            </li>
            <li class="nav-item">
                <a href="index.html" class="nav-link">
                    <i class="fas fa-globe"></i> Ver Sitio Web
                </a>
            </li>
            <li class="nav-item">
                <a href="Php/logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesion
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="main-content">
        <div class="top-bar">
            <button class="toggle-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
            <div class="welcome-text">
                <h1>Hola, <?php echo htmlspecialchars($admin_nombre); ?>!</h1>
                <p><i class="fas fa-calendar"></i> <?php echo date('d/m/Y'); ?></p>
            </div>
            <div style="display: flex; align-items: center; gap: 12px;">
                <span class="admin-badge"><i class="fas fa-crown"></i> Admin</span>
                <a href="Php/logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Salir</a>
            </div>
        </div>
        
        <!-- Dashboard -->
        <div id="section-dashboard" class="section-content">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fas fa-users"></i></div>
                    <h3><?php echo $stats['usuarios']; ?></h3>
                    <p>Usuarios</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fas fa-bed"></i></div>
                    <h3><?php echo $stats['habitaciones']; ?></h3>
                    <p>Habitaciones</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon teal"><i class="fas fa-door-open"></i></div>
                    <h3><?php echo $stats['disponibles']; ?></h3>
                    <p>Disponibles</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple"><i class="fas fa-calendar-alt"></i></div>
                    <h3><?php echo $stats['reservas']; ?></h3>
                    <p>Reservas</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
                    <h3><?php echo $stats['pendientes']; ?></h3>
                    <p>Pendientes</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fas fa-dollar-sign"></i></div>
                    <h3>$<?php echo number_format($stats['ingresos'], 0, ',', '.'); ?></h3>
                    <p>Ingresos</p>
                </div>
            </div>
            
            <!-- Clientes Recientes en Tiempo Real -->
            <div class="content-card" style="margin-bottom: 25px;">
                <div class="card-header">
                    <h2><i class="fas fa-user-clock"></i> Clientes Registrados Recientemente</h2>
                    <span id="lastUpdate" style="color: rgba(255,255,255,0.5); font-size: 0.8rem;">Actualizando...</span>
                </div>
                <div id="clientesRecientes">
                    <?php 
                    $recientes = $conn->query("SELECT id, nombre, apellido, email, fecha_registro FROM usuarios WHERE rol = 'cliente' ORDER BY fecha_registro DESC LIMIT 5");
                    if ($recientes && $recientes->num_rows > 0):
                        while($cliente = $recientes->fetch_assoc()):
                    ?>
                    <div style="display:flex;align-items:center;gap:15px;padding:12px;border-bottom:1px solid rgba(255,255,255,0.05);">
                        <div style="width:40px;height:40px;background:linear-gradient(135deg, #3498db, #2980b9);border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-weight:bold;">
                            <?php echo strtoupper(substr($cliente['nombre'], 0, 1)); ?>
                        </div>
                        <div style="flex:1;">
                            <div style="color:white;font-weight:500;"><?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']); ?></div>
                            <div style="color:rgba(255,255,255,0.5);font-size:0.85rem;"><?php echo htmlspecialchars($cliente['email']); ?></div>
                        </div>
                        <div style="text-align:right;">
                            <div style="color:rgba(255,255,255,0.4);font-size:0.75rem;">Registrado</div>
                            <div style="color:#27ae60;font-size:0.85rem;"><?php echo date('d/m/Y H:i', strtotime($cliente['fecha_registro'])); ?></div>
                        </div>
                    </div>
                    <?php endwhile; else: ?>
                    <div style="text-align:center;padding:30px;color:rgba(255,255,255,0.5);">
                        <i class="fas fa-users" style="font-size:2rem;margin-bottom:10px;display:block;"></i>
                        No hay clientes registrados aun
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="content-card">
                <div class="card-header">
                    <h2><i class="fas fa-calendar-check"></i> Ultimas Reservas</h2>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Habitacion</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Total</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($reservas && $reservas->num_rows > 0):
                                $reservas->data_seek(0);
                                $count = 0;
                                while($res = $reservas->fetch_assoc()): 
                                    if ($count >= 5) break;
                                    $count++;
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($res['nombre'] . ' ' . $res['apellido']); ?></td>
                                <td><?php echo htmlspecialchars($res['numero_habitacion']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($res['fecha_entrada'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($res['fecha_salida'])); ?></td>
                                <td>$<?php echo number_format($res['precio_total'], 0, ',', '.'); ?></td>
                                <td><span class="status-badge status-<?php echo $res['estado']; ?>"><?php echo ucfirst($res['estado']); ?></span></td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="6" style="text-align:center; color:#999; padding:30px;">No hay reservas</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Usuarios -->
        <div id="section-usuarios" class="section-content" style="display: none;">
            <div class="content-card">
                <div class="card-header">
                    <h2><i class="fas fa-users"></i> Gestion de Usuarios</h2>
                    <button class="btn-add" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="fas fa-plus"></i> Nuevo
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $usuarios->data_seek(0);
                            while($user = $usuarios->fetch_assoc()): 
                            ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['nombre'] . ' ' . $user['apellido']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><span class="rol-badge rol-<?php echo $user['rol']; ?>"><?php echo ucfirst($user['rol']); ?></span></td>
                                <td><span class="status-badge status-<?php echo $user['estado']; ?>"><?php echo ucfirst($user['estado']); ?></span></td>
                                <td>
                                    <button class="btn-action btn-edit" onclick='editUser(<?php echo json_encode($user); ?>)'><i class="fas fa-edit"></i></button>
                                    <?php if ($user['id'] != $_SESSION['id'] && $user['rol'] != 'administrador'): ?>
                                    <button class="btn-action btn-delete" onclick="deleteUser(<?php echo $user['id']; ?>)"><i class="fas fa-trash"></i></button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Habitaciones -->
        <div id="section-habitaciones" class="section-content" style="display: none;">
            <div class="content-card">
                <div class="card-header">
                    <h2><i class="fas fa-bed"></i> Gestion de Habitaciones</h2>
                    <button class="btn-add" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                        <i class="fas fa-plus"></i> Nueva
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Numero</th>
                                <th>Tipo</th>
                                <th>Capacidad</th>
                                <th>Precio/Noche</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $habitaciones->data_seek(0);
                            while($hab = $habitaciones->fetch_assoc()): 
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($hab['numero_habitacion']); ?></strong></td>
                                <td><?php echo ucfirst(htmlspecialchars($hab['tipo'])); ?></td>
                                <td><?php echo $hab['capacidad']; ?> pers.</td>
                                <td>$<?php echo number_format($hab['precio_noche'], 0, ',', '.'); ?></td>
                                <td><span class="status-badge status-<?php echo $hab['estado']; ?>"><?php echo ucfirst($hab['estado']); ?></span></td>
                                <td>
                                    <button class="btn-action btn-edit" onclick='editRoom(<?php echo json_encode($hab); ?>)'><i class="fas fa-edit"></i></button>
                                    <button class="btn-action btn-delete" onclick="deleteRoom(<?php echo $hab['id']; ?>)"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Reservas -->
        <div id="section-reservas" class="section-content" style="display: none;">
            <div class="content-card">
                <div class="card-header">
                    <h2><i class="fas fa-calendar-check"></i> Gestion de Reservas</h2>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Habitacion</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($reservas && $reservas->num_rows > 0):
                                $reservas->data_seek(0);
                                while($res = $reservas->fetch_assoc()): 
                            ?>
                            <tr>
                                <td><?php echo $res['id']; ?></td>
                                <td><?php echo htmlspecialchars($res['nombre'] . ' ' . $res['apellido']); ?></td>
                                <td><?php echo htmlspecialchars($res['numero_habitacion']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($res['fecha_entrada'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($res['fecha_salida'])); ?></td>
                                <td>$<?php echo number_format($res['precio_total'], 0, ',', '.'); ?></td>
                                <td><span class="status-badge status-<?php echo $res['estado']; ?>"><?php echo ucfirst($res['estado']); ?></span></td>
                                <td>
                                    <select class="form-select form-select-sm" style="width:120px;padding:4px;font-size:0.8rem;" onchange="updateReservation(<?php echo $res['id']; ?>, this.value)">
                                        <option value="pendiente" <?php echo $res['estado']=='pendiente'?'selected':''; ?>>Pendiente</option>
                                        <option value="confirmada" <?php echo $res['estado']=='confirmada'?'selected':''; ?>>Confirmada</option>
                                        <option value="completada" <?php echo $res['estado']=='completada'?'selected':''; ?>>Completada</option>
                                        <option value="cancelada" <?php echo $res['estado']=='cancelada'?'selected':''; ?>>Cancelada</option>
                                    </select>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="8" style="text-align:center;color:#999;padding:30px;">No hay reservas</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Nuevo Usuario -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-plus"></i> Nuevo Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="Php/regUserInt.php" method="POST">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre</label>
                                <input type="text" class="form-control" name="nombre" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Apellido</label>
                                <input type="text" class="form-control" name="apellido" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tipo Doc.</label>
                                <select class="form-select" name="tipo_documento" required>
                                    <option value="CC">Cedula</option>
                                    <option value="CE">Extranjeria</option>
                                    <option value="TI">Tarjeta ID</option>
                                    <option value="PP">Pasaporte</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Numero Doc.</label>
                                <input type="text" class="form-control" name="numero_documento" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Genero</label>
                                <select class="form-select" name="genero" required>
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                    <option value="O">Otro</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Usuario</label>
                                <input type="text" class="form-control" name="username" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contrasena</label>
                                <input type="password" class="form-control" name="password" required minlength="6">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Rol</label>
                                <select class="form-select" name="rol" required>
                                    <option value="cliente">Cliente</option>
                                    <option value="administrador">Administrador</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal Nueva Habitacion -->
    <div class="modal fade" id="addRoomModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-bed"></i> Nueva Habitacion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="Php/habitaciones/crearhabitacion.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Numero</label>
                            <input type="text" class="form-control" name="numero_habitacion" required placeholder="Ej: 101">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipo</label>
                            <select class="form-select" name="tipo" required>
                                <option value="sencilla">Sencilla</option>
                                <option value="doble">Doble</option>
                                <option value="suite">Suite</option>
                                <option value="familiar">Familiar</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripcion</label>
                            <textarea class="form-control" name="descripcion" rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Capacidad</label>
                                <input type="number" class="form-control" name="capacidad" required min="1" value="2">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Precio/Noche</label>
                                <input type="number" class="form-control" name="precio_noche" required min="0">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal Editar Usuario -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-edit"></i> Editar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="Php/actualizar.php" method="POST">
                    <input type="hidden" name="id" id="edit_user_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre</label>
                                <input type="text" class="form-control" name="nombre" id="edit_nombre" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Apellido</label>
                                <input type="text" class="form-control" name="apellido" id="edit_apellido" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="edit_email" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Rol</label>
                                <select class="form-select" name="rol" id="edit_rol" required>
                                    <option value="cliente">Cliente</option>
                                    <option value="administrador">Administrador</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Estado</label>
                                <select class="form-select" name="estado" id="edit_estado" required>
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal Editar Habitacion -->
    <div class="modal fade" id="editRoomModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-bed"></i> Editar Habitacion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="Php/habitaciones/actualizarhabitacion.php" method="POST">
                    <input type="hidden" name="id" id="edit_room_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Numero</label>
                            <input type="text" class="form-control" name="numero_habitacion" id="edit_numero_habitacion" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipo</label>
                            <select class="form-select" name="tipo" id="edit_tipo" required>
                                <option value="sencilla">Sencilla</option>
                                <option value="doble">Doble</option>
                                <option value="suite">Suite</option>
                                <option value="familiar">Familiar</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Capacidad</label>
                                <input type="number" class="form-control" name="capacidad" id="edit_capacidad" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Precio/Noche</label>
                                <input type="number" class="form-control" name="precio_noche" id="edit_precio_noche" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Estado</label>
                            <select class="form-select" name="estado" id="edit_estado_hab" required>
                                <option value="disponible">Disponible</option>
                                <option value="ocupada">Ocupada</option>
                                <option value="mantenimiento">Mantenimiento</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showSection(section) {
            document.querySelectorAll('.section-content').forEach(s => s.style.display = 'none');
            document.getElementById('section-' + section).style.display = 'block';
            document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
            event.target.closest('.nav-link').classList.add('active');
        }
        
        function toggleSidebar() { document.querySelector('.sidebar').classList.toggle('active'); }
        
        function editUser(user) {
            document.getElementById('edit_user_id').value = user.id;
            document.getElementById('edit_nombre').value = user.nombre;
            document.getElementById('edit_apellido').value = user.apellido;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_rol').value = user.rol;
            document.getElementById('edit_estado').value = user.estado;
            new bootstrap.Modal(document.getElementById('editUserModal')).show();
        }
        
        function deleteUser(id) {
            if (confirm('Eliminar este usuario?')) window.location.href = 'Php/eliminar.php?id=' + id;
        }
        
        function editRoom(room) {
            document.getElementById('edit_room_id').value = room.id;
            document.getElementById('edit_numero_habitacion').value = room.numero_habitacion;
            document.getElementById('edit_tipo').value = room.tipo;
            document.getElementById('edit_capacidad').value = room.capacidad;
            document.getElementById('edit_precio_noche').value = room.precio_noche;
            document.getElementById('edit_estado_hab').value = room.estado;
            new bootstrap.Modal(document.getElementById('editRoomModal')).show();
        }
        
        function deleteRoom(id) {
            if (confirm('Eliminar esta habitacion?')) window.location.href = 'Php/habitaciones/eliminarhabitacion.php?id=' + id;
        }
        
        function updateReservation(id, status) {
            window.location.href = 'Php/reservas/actualizarreserva.php?id=' + id + '&estado=' + status;
        }
        
        setTimeout(() => { document.querySelectorAll('.alert-floating').forEach(a => a.remove()); }, 4000);
        
        // ============================================
        // ACTUALIZACION EN TIEMPO REAL
        // ============================================
        
        // Actualizar estadisticas cada 30 segundos
        function actualizarEstadisticas() {
            fetch('Php/api/estadisticas.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Actualizar contadores
                        document.querySelectorAll('.stat-card h3').forEach((el, index) => {
                            const keys = ['usuarios', 'habitaciones', 'disponibles', 'reservas', 'pendientes', 'ingresos'];
                            if (keys[index] === 'ingresos') {
                                el.textContent = '$' + formatNumber(data.stats[keys[index]]);
                            } else if (data.stats[keys[index]] !== undefined) {
                                el.textContent = data.stats[keys[index]];
                            }
                        });
                    }
                })
                .catch(err => console.log('Error actualizando estadisticas'));
        }
        
        // Formatear numeros con separador de miles
        function formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }
        
        // Actualizar cada 30 segundos
        setInterval(actualizarEstadisticas, 30000);
        
        // Actualizar lista de clientes cada 15 segundos
        function actualizarClientes() {
            fetch('Php/api/usuarios.php?action=clientes')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.clientes) {
                        let html = '';
                        data.clientes.slice(0, 5).forEach(cliente => {
                            html += `
                            <div style="display:flex;align-items:center;gap:15px;padding:12px;border-bottom:1px solid rgba(255,255,255,0.05);">
                                <div style="width:40px;height:40px;background:linear-gradient(135deg, #3498db, #2980b9);border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-weight:bold;">
                                    ${cliente.nombre.charAt(0).toUpperCase()}
                                </div>
                                <div style="flex:1;">
                                    <div style="color:white;font-weight:500;">${cliente.nombre} ${cliente.apellido}</div>
                                    <div style="color:rgba(255,255,255,0.5);font-size:0.85rem;">${cliente.email}</div>
                                </div>
                                <div style="text-align:right;">
                                    <div style="color:rgba(255,255,255,0.4);font-size:0.75rem;">Registrado</div>
                                    <div style="color:#27ae60;font-size:0.85rem;">${cliente.fecha_registro_formato}</div>
                                </div>
                            </div>`;
                        });
                        
                        if (html === '') {
                            html = '<div style="text-align:center;padding:30px;color:rgba(255,255,255,0.5);"><i class="fas fa-users" style="font-size:2rem;margin-bottom:10px;display:block;"></i>No hay clientes registrados aun</div>';
                        }
                        
                        document.getElementById('clientesRecientes').innerHTML = html;
                        document.getElementById('lastUpdate').textContent = 'Actualizado: ' + new Date().toLocaleTimeString();
                    }
                })
                .catch(err => console.log('Error actualizando clientes'));
        }
        
        setInterval(actualizarClientes, 15000);
        
        // Indicador de tiempo real
        const indicator = document.createElement('div');
        indicator.innerHTML = '<i class="fas fa-circle" style="color:#27ae60;font-size:8px;"></i> Tiempo Real';
        indicator.style.cssText = 'position:fixed;bottom:15px;right:15px;background:rgba(0,0,0,0.7);color:white;padding:8px 15px;border-radius:20px;font-size:12px;z-index:999;';
        document.body.appendChild(indicator);
    </script>
</body>
</html>
<?php $conn->close(); ?>
