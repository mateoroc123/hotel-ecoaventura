# Hotel EcoAventura

Plataforma web de reservas de hoteles ecológicos. Permite a los clientes registrarse, buscar un destino, reservar una habitación y gestionar sus reservas, y a los administradores gestionar usuarios, habitaciones y reservas desde un panel.

Proyecto universitario — Ingeniería de Sistemas, Universidad EAN.

## Funcionalidades

**Clientes**
- Registro de usuarios con validaciones e inicio de sesión.
- Recuperación de contraseña: se envía un código de 6 dígitos al correo (PHPMailer + SMTP) que expira a los 15 minutos.
- Búsqueda de habitaciones por destino (Cartagena, Santa Marta, Medellín, Bogotá, Cali y San Andrés), fechas y huéspedes.
- Reserva de habitaciones, y consulta y cancelación de reservas desde el panel del cliente.

**Administrador**
- Panel con gestión de usuarios (CRUD).
- Gestión de habitaciones (crear, listar, actualizar, eliminar).
- Gestión del estado de las reservas (pendiente, confirmada, cancelada, completada).

<img width="1901" height="900" alt="{F1BD5CF6-A7C5-4CC1-A05E-4FA3A940E065}" src="https://github.com/user-attachments/assets/2db99710-5c99-4b4d-8fd1-e9166abb6267" />
<img width="1920" height="902" alt="{68974900-B2F5-4760-ACC9-94C6F0C3D1DB}" src="https://github.com/user-attachments/assets/eda779eb-eec2-4b5b-8c3d-47222abaa09d" />
<img width="1920" height="902" alt="{E2109E60-8D03-451D-B5B1-87FE9809CE47}" src="https://github.com/user-attachments/assets/76002eb4-4f8c-4604-ad98-48b36ec10756" />
<img width="1920" height="902" alt="{C8CC4AA1-2027-4FCF-9F78-CF9D8502D456}" src="https://github.com/user-attachments/assets/0c0a8b44-0b4e-4bd0-8adb-b9929b6835bf" />

## Tecnologías

| Capa | Tecnología |
| --- | --- |
| Frontend | HTML5, CSS3, JavaScript |
| Backend | PHP 8 (mysqli, sesiones, consultas preparadas) |
| Base de datos | MySQL / MariaDB (administrada con phpMyAdmin) |
| Correo | PHPMailer |
| Entorno local | XAMPP |

## Estructura

```
hotel-ecoaventura/
├── index.html                 # Página principal y buscador
├── login.html / regUser.html  # Inicio de sesión y registro
├── recuperar_password.html    # Recuperación de contraseña
├── dashboardCliente.php       # Panel del cliente
├── panelAdmin.php             # Panel del administrador
├── Css/  Js/  img/
├── Php/
│   ├── conexion.php           # Conexión a MySQL
│   ├── config_smtp.example.php# Plantilla de configuración de correo
│   ├── habitaciones/          # CRUD de habitaciones
│   ├── reservas/              # CRUD de reservas
│   └── PHPMailer-master/
└── database/
    ├── hotel.sql              # Estructura de la base de datos + habitaciones
    └── agregar_campos_recuperacion.sql
```

## Instalación local (XAMPP)

1. Instala [XAMPP](https://www.apachefriends.org/) e inicia **Apache** y **MySQL** desde su panel de control.
2. Copia la carpeta del proyecto en `C:\xampp\htdocs\hotel-ecoaventura`.
3. Abre `http://localhost/phpmyadmin`, crea una base de datos llamada `hotel` (cotejamiento `utf8mb4_spanish_ci`) y, en la pestaña **Importar**, selecciona `database/hotel.sql`.
4. Configura el correo: copia `Php/config_smtp.example.php` como `Php/config_smtp.php` y escribe tu correo y una [contraseña de aplicación de Google](https://myaccount.google.com/apppasswords).
5. Si tu MySQL tiene usuario o contraseña distintos a los de XAMPP, ajústalos en `Php/conexion.php`.
6. Abre `http://localhost/hotel-ecoaventura/index.html`.

### Crear un administrador

Regístrate desde la página y luego, en phpMyAdmin, ejecuta:

```sql
UPDATE usuarios SET rol = 'administrador' WHERE username = 'tu_usuario';
```

## Seguridad

- Las contraseñas se guardan con `password_hash()` (bcrypt) y se validan con `password_verify()`.
- Las credenciales SMTP no están en el repositorio (`.gitignore`).
- `Php/conexion.php` usa la configuración por defecto de XAMPP (`root` sin contraseña): es válida solo para desarrollo local.

## Autor

**Mateo Rocha Castiblanco** — Estudiante de Ingeniería de Sistemas, Universidad EAN.
[LinkedIn](https://www.linkedin.com/in/mateo-rocha-castiblanco-233a3430b/)
