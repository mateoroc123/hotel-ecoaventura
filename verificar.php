<?php
/*
|--------------------------------------------------------------------------
| Verificador de Instalación - Hotel EcoAventura
|--------------------------------------------------------------------------
| Este archivo verifica que todo esté correctamente instalado
*/

session_start();
$problemas = [];
$verificaciones = [];

// Verificar conexión a BD
try {
    $conn = mysqli_connect("localhost", "root", "", "hotel_ecoaventura");
    
    if (!$conn) {
        $problemas[] = "No se puede conectar a la base de datos";
    } else {
        $verificaciones[] = "✓ Conexión a base de datos exitosa";
        
        // Verificar tablas
        $tablas_requeridas = ['usuarios', 'habitaciones', 'reservas'];
        
        foreach ($tablas_requeridas as $tabla) {
            $result = mysqli_query($conn, "SHOW TABLES LIKE '$tabla'");
            if (mysqli_num_rows($result) > 0) {
                $verificaciones[] = "✓ Tabla '$tabla' existe";
            } else {
                $problemas[] = "Tabla '$tabla' no existe. Importa database.sql";
            }
        }
        
        // Verificar usuario admin
        $result = mysqli_query($conn, "SELECT * FROM usuarios WHERE loginUser = 'admin'");
        if (mysqli_num_rows($result) > 0) {
            $verificaciones[] = "✓ Usuario administrador existe";
        } else {
            $problemas[] = "Usuario admin no encontrado";
        }
        
        mysqli_close($conn);
    }
} catch (Exception $e) {
    $problemas[] = "Error al conectar: " . $e->getMessage();
}

// Verificar archivos
$archivos_requeridos = [
    'hotel_ecoaventura-1/index.html',
    'hotel_ecoaventura-1/Php/conexion.php',
    'hotel_ecoaventura-1/Php/registro.php',
    'hotel_ecoaventura-1/Php/autenticacion.php',
    'panelAdmin.php',
    'database.sql'
];

foreach ($archivos_requeridos as $archivo) {
    if (file_exists($archivo)) {
        $verificaciones[] = "✓ Archivo '$archivo' encontrado";
    } else {
        $problemas[] = "Archivo '$archivo' no encontrado";
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificador de Instalación | Hotel EcoAventura</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Lexend', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .section {
            margin-bottom: 30px;
        }
        
        .section-title {
            color: #667eea;
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .item {
            padding: 10px 15px;
            background: #f5f5f5;
            border-left: 4px solid #51cf66;
            margin-bottom: 10px;
            border-radius: 4px;
            font-size: 14px;
            color: #333;
        }
        
        .item.error {
            background: #ffe0e0;
            border-left-color: #ff6b6b;
            color: #c92a2a;
        }
        
        .status {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
            text-align: center;
        }
        
        .status.success {
            background: #d3f9d8;
            color: #2b8a3e;
        }
        
        .status.warning {
            background: #fff3cd;
            color: #997404;
        }
        
        .actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        
        .btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            text-align: center;
            display: inline-block;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
        }
        
        .btn-secondary {
            background: #e0e0e0;
            color: #333;
        }
        
        .btn-secondary:hover {
            background: #d0d0d0;
        }
        
        .icon {
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏨 Hotel EcoAventura</h1>
        <p class="subtitle">Verificador de Instalación</p>
        
        <?php if (empty($problemas)): ?>
            <div class="status success">
                ✓ ¡Sistema completamente instalado y funcionando!
            </div>
        <?php else: ?>
            <div class="status warning">
                ⚠ Se encontraron <?php echo count($problemas); ?> problema(s)
            </div>
        <?php endif; ?>
        
        <?php if (!empty($verificaciones)): ?>
            <div class="section">
                <div class="section-title">
                    <span class="icon">✓</span> Verificaciones Exitosas
                </div>
                <?php foreach ($verificaciones as $item): ?>
                    <div class="item"><?php echo htmlspecialchars($item); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($problemas)): ?>
            <div class="section">
                <div class="section-title">
                    <span class="icon">✗</span> Problemas Detectados
                </div>
                <?php foreach ($problemas as $item): ?>
                    <div class="item error"><?php echo htmlspecialchars($item); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="section">
            <div class="section-title">
                <span class="icon">📝</span> Próximos Pasos
            </div>
            <?php if (empty($problemas)): ?>
                <div class="item">
                    1. Ve a: <strong>http://localhost/hotel_ecoaventura/hotel_ecoaventura-1/</strong>
                </div>
                <div class="item">
                    2. Usa las credenciales: <strong>admin / admin123</strong>
                </div>
                <div class="item">
                    3. ¡Disfruta del sistema! 🎉
                </div>
            <?php else: ?>
                <div class="item">
                    1. Lee el archivo <strong>INSTALACION.md</strong>
                </div>
                <div class="item">
                    2. Sigue los pasos para importar la base de datos
                </div>
                <div class="item">
                    3. Ejecuta este verificador nuevamente
                </div>
            <?php endif; ?>
        </div>
        
        <div class="actions">
            <?php if (empty($problemas)): ?>
                <a href="hotel_ecoaventura-1/index.html" class="btn btn-primary">
                    Ir a la Aplicación →
                </a>
            <?php endif; ?>
            <button class="btn btn-secondary" onclick="location.reload()">
                Verificar de Nuevo
            </button>
        </div>
    </div>
</body>
</html>
