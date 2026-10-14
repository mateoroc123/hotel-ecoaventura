<?php
// ============================================
// DASHBOARD CLIENTE - Hotel EcoAventura
// ============================================
session_start();

// Verificar si está logueado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.html?error=" . urlencode("Debes iniciar sesión"));
    exit();
}

// Verificar que sea cliente
if ($_SESSION['rol'] !== 'cliente') {
    header("Location: panelAdmin.php");
    exit();
}

// Incluir conexión
include("Php/conexion.php");

// Obtener datos del usuario
$user_id = $_SESSION['id'];
$nombre = $_SESSION['nombre'];
$apellido = $_SESSION['apellido'];
$genero = $_SESSION['genero'] ?? 'O';

// Determinar saludo segun genero
$saludo = ($genero === 'F') ? 'Bienvenida' : 'Bienvenido';

// Obtener reservas del usuario
$sql_reservas = "SELECT r.*, h.numero_habitacion, h.tipo, h.imagen 
                 FROM reservas r 
                 INNER JOIN habitaciones h ON r.id_habitacion = h.id 
                 WHERE r.id_usuario = ? 
                 ORDER BY r.fecha_reserva DESC";
$stmt = $conn->prepare($sql_reservas);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$reservas = $stmt->get_result();

// Obtener habitaciones disponibles
$sql_habitaciones = "SELECT * FROM habitaciones WHERE estado = 'disponible' ORDER BY precio_noche ASC";
$habitaciones = $conn->query($sql_habitaciones);

// Contar reservas activas
$sql_count = "SELECT COUNT(*) as total FROM reservas WHERE id_usuario = ? AND estado IN ('pendiente', 'confirmada')";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->bind_param("i", $user_id);
$stmt_count->execute();
$count_result = $stmt_count->get_result()->fetch_assoc();
$reservas_activas = $count_result['total'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Panel - Hotel EcoAventura</title>
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
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--cream) 0%, #e8e4db 100%);
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 280px;
            background: linear-gradient(180deg, var(--primary-green) 0%, var(--light-green) 100%);
            padding: 20px;
            z-index: 1000;
            box-shadow: 4px 0 15px rgba(0,0,0,0.1);
        }
        
        .sidebar-logo {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            margin-bottom: 30px;
        }
        
        .sidebar-logo img {
            width: 120px;
            height: auto;
        }
        
        .sidebar-logo h3 {
            color: white;
            margin-top: 10px;
            font-size: 1.2rem;
        }
        
        .nav-menu {
            list-style: none;
            padding: 0;
        }
        
        .nav-item {
            margin-bottom: 10px;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover, .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
            transform: translateX(5px);
        }
        
        .nav-link i {
            width: 25px;
            margin-right: 15px;
            font-size: 1.1rem;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 280px;
            padding: 30px;
        }
        
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 20px 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .welcome-text h1 {
            color: var(--primary-green);
            font-size: 1.8rem;
            margin-bottom: 5px;
        }
        
        .welcome-text p {
            color: #666;
            font-size: 0.9rem;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-avatar {
            width: 50px;
            height: 50px;
            background: var(--primary-green);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.3rem;
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        
        .stat-icon.green { background: linear-gradient(135deg, var(--primary-green), var(--light-green)); }
        .stat-icon.orange { background: linear-gradient(135deg, #e67e22, #f39c12); }
        .stat-icon.blue { background: linear-gradient(135deg, #3498db, #2980b9); }
        
        .stat-info h3 {
            font-size: 2rem;
            color: var(--dark);
            margin-bottom: 5px;
        }
        
        .stat-info p {
            color: #666;
            font-size: 0.9rem;
        }
        
        /* Cards */
        .content-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--cream);
        }
        
        .card-header h2 {
            color: var(--primary-green);
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        /* Rooms Grid */
        .rooms-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }
        
        .room-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border: 1px solid #eee;
        }
        
        .room-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }
        
        .room-image {
            height: 180px;
            overflow: hidden;
        }
        
        .room-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .room-card:hover .room-image img {
            transform: scale(1.1);
        }
        
        .room-info {
            padding: 20px;
        }
        
        .room-type {
            display: inline-block;
            background: var(--cream);
            color: var(--primary-green);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .room-info h3 {
            color: var(--dark);
            margin-bottom: 10px;
        }
        
        .room-info p {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        
        .room-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .room-price {
            color: var(--accent-orange);
            font-size: 1.3rem;
            font-weight: 700;
        }
        
        .room-price span {
            font-size: 0.8rem;
            color: #999;
            font-weight: normal;
        }
        
        .btn-reserve {
            background: var(--primary-green);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
        }
        
        .btn-reserve:hover {
            background: var(--light-green);
            transform: scale(1.05);
        }
        
        /* Reservations Table */
        .table-responsive {
            overflow-x: auto;
        }
        
        .reservations-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .reservations-table th {
            background: var(--cream);
            color: var(--primary-green);
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        .reservations-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        
        .reservations-table tr:hover {
            background: #fafafa;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-pendiente { background: #fff3cd; color: #856404; }
        .status-confirmada { background: #d4edda; color: #155724; }
        .status-cancelada { background: #f8d7da; color: #721c24; }
        .status-completada { background: #cce5ff; color: #004085; }
        
        .btn-cancel {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }
        
        .btn-cancel:hover {
            background: #c82333;
        }
        
        .btn-logout {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-logout:hover {
            background: #c82333;
            color: white;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        /* Modal */
        .modal-header {
            background: var(--primary-green);
            color: white;
        }
        
        .modal-header .btn-close {
            filter: invert(1);
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 0.2rem rgba(45, 90, 39, 0.1);
        }
        
        .btn-primary {
            background: var(--primary-green);
            border-color: var(--primary-green);
            border-radius: 25px;
            padding: 12px 30px;
        }
        
        .btn-primary:hover {
            background: var(--light-green);
            border-color: var(--light-green);
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .toggle-sidebar {
                display: block !important;
            }
        }
        
        .toggle-sidebar {
            display: none;
            background: var(--primary-green);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 10px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-logo">
            <img src="img/logo.png" alt="Hotel EcoAventura">
            <h3>Hotel EcoAventura</h3>
        </div>
        
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="#dashboard" class="nav-link active">
                    <i class="fas fa-home"></i>
                    <span>Inicio</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#habitaciones" class="nav-link">
                    <i class="fas fa-bed"></i>
                    <span>Habitaciones</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#reservas" class="nav-link">
                    <i class="fas fa-calendar-check"></i>
                    <span>Mis Reservas</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="index.html" class="nav-link">
                    <i class="fas fa-globe"></i>
                    <span>Ver Sitio Web</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="Php/logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Cerrar Sesion</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <button class="toggle-sidebar" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
<div class="welcome-text">
<h1><?php echo $saludo; ?>, <?php echo htmlspecialchars($nombre); ?>!</h1>
<p><i class="fas fa-leaf"></i> Disfruta de la naturaleza en Hotel EcoAventura</p>
</div>
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <strong><?php echo htmlspecialchars($nombre . ' ' . $apellido); ?></strong>
                    <br><small style="color: #666;"><?php echo htmlspecialchars($_SESSION['email']); ?></small>
                </div>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="stats-grid" id="dashboard">
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $reservas_activas; ?></h3>
                    <p>Reservas Activas</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-bed"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $habitaciones->num_rows; ?></h3>
                    <p>Habitaciones Disponibles</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-tree"></i>
                </div>
                <div class="stat-info">
                    <h3>100%</h3>
                    <p>Eco-Friendly</p>
                </div>
            </div>
        </div>
        
        <!-- Available Rooms -->
        <div class="content-card" id="habitaciones">
            <div class="card-header">
                <h2><i class="fas fa-bed"></i> Habitaciones Disponibles</h2>
            </div>
            
            <div class="rooms-grid">
                <?php 
                $habitaciones->data_seek(0);
                while($hab = $habitaciones->fetch_assoc()): 
                ?>
                <div class="room-card">
                    <div class="room-image">
                        <img src="<?php echo htmlspecialchars($hab['imagen'] ?? 'img/images.jpg'); ?>" alt="Habitacion <?php echo htmlspecialchars($hab['numero_habitacion']); ?>">
                    </div>
                    <div class="room-info">
                        <span class="room-type"><?php echo ucfirst(htmlspecialchars($hab['tipo'])); ?></span>
                        <h3>Habitacion <?php echo htmlspecialchars($hab['numero_habitacion']); ?></h3>
                        <p><?php echo htmlspecialchars(substr($hab['descripcion'] ?? '', 0, 80)); ?>...</p>
                        <div class="room-meta">
                            <div class="room-price">
                                $<?php echo number_format($hab['precio_noche'], 0, ',', '.'); ?>
                                <span>/noche</span>
                            </div>
                            <button class="btn-reserve" onclick="openReserveModal(<?php echo $hab['id']; ?>, '<?php echo htmlspecialchars($hab['numero_habitacion']); ?>', <?php echo $hab['precio_noche']; ?>)">
                                Reservar
                            </button>
                        </div>
                        <small style="color: #888;"><i class="fas fa-users"></i> Capacidad: <?php echo $hab['capacidad']; ?> personas</small>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        
        <!-- My Reservations -->
        <div class="content-card" id="reservas">
            <div class="card-header">
                <h2><i class="fas fa-calendar-check"></i> Mis Reservas</h2>
            </div>
            
            <?php if ($reservas->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="reservations-table">
                    <thead>
                        <tr>
                            <th>Habitacion</th>
                            <th>Tipo</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Huespedes</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($res = $reservas->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($res['numero_habitacion']); ?></strong></td>
                            <td><?php echo ucfirst(htmlspecialchars($res['tipo'])); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($res['fecha_entrada'])); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($res['fecha_salida'])); ?></td>
                            <td><?php echo $res['num_huespedes']; ?></td>
                            <td><strong>$<?php echo number_format($res['precio_total'], 0, ',', '.'); ?></strong></td>
                            <td>
                                <span class="status-badge status-<?php echo $res['estado']; ?>">
                                    <?php echo ucfirst($res['estado']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($res['estado'] === 'pendiente' || $res['estado'] === 'confirmada'): ?>
                                <button class="btn-cancel" onclick="cancelReservation(<?php echo $res['id']; ?>)">
                                    <i class="fas fa-times"></i> Cancelar
                                </button>
                                <?php else: ?>
                                <span style="color: #999;">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <h3>No tienes reservas aun</h3>
                <p>Explora nuestras habitaciones y haz tu primera reserva!</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Reserve Modal -->
    <div class="modal fade" id="reserveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-calendar-plus"></i> Nueva Reserva</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="Php/reservas/crearreserva.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id_habitacion" id="modal_id_habitacion">
                        <input type="hidden" name="precio_noche" id="modal_precio_noche">
                        
                        <div class="mb-3">
                            <label class="form-label">Habitacion Seleccionada</label>
                            <input type="text" class="form-control" id="modal_habitacion" readonly>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha Check-in</label>
                                <input type="date" class="form-control" name="fecha_entrada" id="fecha_entrada" required min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha Check-out</label>
                                <input type="date" class="form-control" name="fecha_salida" id="fecha_salida" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Numero de Huespedes</label>
                            <select class="form-select" name="num_huespedes" required>
                                <option value="1">1 persona</option>
                                <option value="2">2 personas</option>
                                <option value="3">3 personas</option>
                                <option value="4">4 personas</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Notas adicionales (opcional)</label>
                            <textarea class="form-control" name="notas" rows="2" placeholder="Requerimientos especiales..."></textarea>
                        </div>
                        
                        <div class="alert alert-info">
                            <strong>Precio por noche:</strong> $<span id="modal_precio_display">0</span>
                            <br>
                            <strong>Total estimado:</strong> $<span id="total_estimado">0</span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> Confirmar Reserva
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let precioNoche = 0;
        
        function openReserveModal(id, numero, precio) {
            document.getElementById('modal_id_habitacion').value = id;
            document.getElementById('modal_habitacion').value = 'Habitacion ' + numero;
            document.getElementById('modal_precio_noche').value = precio;
            document.getElementById('modal_precio_display').textContent = precio.toLocaleString('es-CO');
            precioNoche = precio;
            
            // Reset dates
            document.getElementById('fecha_entrada').value = '';
            document.getElementById('fecha_salida').value = '';
            document.getElementById('total_estimado').textContent = '0';
            
            const modal = new bootstrap.Modal(document.getElementById('reserveModal'));
            modal.show();
        }
        
        // Calculate total when dates change
        document.getElementById('fecha_entrada').addEventListener('change', calcularTotal);
        document.getElementById('fecha_salida').addEventListener('change', calcularTotal);
        
        function calcularTotal() {
            const entrada = new Date(document.getElementById('fecha_entrada').value);
            const salida = new Date(document.getElementById('fecha_salida').value);
            
            if (entrada && salida && salida > entrada) {
                const dias = Math.ceil((salida - entrada) / (1000 * 60 * 60 * 24));
                const total = dias * precioNoche;
                document.getElementById('total_estimado').textContent = total.toLocaleString('es-CO');
            }
        }
        
        function cancelReservation(id) {
            if (confirm('¿Estas seguro de cancelar esta reserva?')) {
                window.location.href = 'Php/reservas/cancelarreserva.php?id=' + id;
            }
        }
        
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('active');
        }
        
        // Smooth scroll for nav links
        document.querySelectorAll('.nav-link[href^="#"]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
                // Update active state
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            });
        });
        
        // Cargar reservas de localStorage (hechas desde index.html)
        function cargarReservasLocalStorage() {
            const reservasGuardadas = localStorage.getItem('misReservas');
            if (!reservasGuardadas) return;
            
            const reservas = JSON.parse(reservasGuardadas);
            if (reservas.length === 0) return;
            
            // Buscar la tabla de reservas o crearla si no existe
            let tablaContainer = document.querySelector('#reservas .table-responsive');
            let tbody;
            
            if (!tablaContainer) {
                // Si no hay tabla (empty state), crear una
                const emptyState = document.querySelector('#reservas .empty-state');
                if (emptyState) {
                    emptyState.style.display = 'none';
                }
                
                const reservasCard = document.getElementById('reservas');
                const tableHTML = `
                    <div class="table-responsive">
                        <table class="reservations-table">
                            <thead>
                                <tr>
                                    <th>Habitacion</th>
                                    <th>Tipo</th>
                                    <th>Check-in</th>
                                    <th>Check-out</th>
                                    <th>Huespedes</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-reservas-local"></tbody>
                        </table>
                    </div>
                `;
                reservasCard.insertAdjacentHTML('beforeend', tableHTML);
                tbody = document.getElementById('tbody-reservas-local');
            } else {
                tbody = tablaContainer.querySelector('tbody');
            }
            
            // Formatear fecha
            const formatearFecha = (fecha) => {
                const d = new Date(fecha + 'T00:00:00');
                return d.toLocaleDateString('es-CO', { day: '2-digit', month: '2-digit', year: 'numeric' });
            };
            
            // Agregar filas de reservas de localStorage
            reservas.forEach(reserva => {
                const fila = document.createElement('tr');
                fila.innerHTML = `
                    <td><strong>${reserva.habitacion}</strong></td>
                    <td>${reserva.tipo}</td>
                    <td>${formatearFecha(reserva.checkin)}</td>
                    <td>${formatearFecha(reserva.checkout)}</td>
                    <td>${reserva.huespedes}</td>
                    <td><strong>$${reserva.total.toLocaleString('es-CO')}</strong></td>
                    <td><span class="status-badge status-completada">${reserva.estado}</span></td>
                    <td><span style="color: #999;">-</span></td>
                `;
                tbody.appendChild(fila);
            });
        }
        
        // Ejecutar al cargar la pagina
        document.addEventListener('DOMContentLoaded', function() {
            cargarReservasLocalStorage();
            
            // Si la URL tiene hash #mis-reservas, hacer scroll a esa seccion
            if (window.location.hash === '#mis-reservas') {
                const reservasSection = document.getElementById('reservas');
                if (reservasSection) {
                    setTimeout(() => {
                        reservasSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 300);
                }
            }
        });
    </script>
</body>
</html>
<?php
$stmt->close();
$stmt_count->close();
$conn->close();
?>
