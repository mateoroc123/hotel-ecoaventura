-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 21-04-2026 a las 19:47:12
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `hotel`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `habitaciones`
--

CREATE TABLE `habitaciones` (
  `id` int(11) NOT NULL,
  `numero_habitacion` varchar(10) NOT NULL COMMENT 'Numero de habitacion (ej: 101, 102)',
  `tipo` enum('sencilla','doble','suite','familiar') NOT NULL COMMENT 'Tipo de habitacion',
  `descripcion` text DEFAULT NULL COMMENT 'Descripcion de la habitacion',
  `capacidad` int(11) NOT NULL DEFAULT 2 COMMENT 'Numero maximo de huespedes',
  `precio_noche` decimal(10,2) NOT NULL COMMENT 'Precio por noche en pesos',
  `imagen` varchar(255) DEFAULT NULL COMMENT 'Ruta de imagen de la habitacion',
  `estado` enum('disponible','ocupada','mantenimiento') NOT NULL DEFAULT 'disponible',
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `habitaciones`
--

INSERT INTO `habitaciones` (`id`, `numero_habitacion`, `tipo`, `descripcion`, `capacidad`, `precio_noche`, `imagen`, `estado`, `fecha_creacion`) VALUES
(1, '101', 'sencilla', 'Habitacion sencilla con vista al jardin, cama individual, bano privado, WiFi y TV.', 1, 80000.00, 'img/images.jpg', 'disponible', '2026-04-10 22:32:38'),
(2, '102', 'doble', 'Habitacion doble con dos camas, aire acondicionado, minibar, WiFi y TV.', 2, 120000.00, 'img/GM-interior-cama-2-2048x1365-1.webp', 'disponible', '2026-04-10 22:32:38'),
(3, '201', 'suite', 'Suite de lujo con sala de estar, jacuzzi, vista panoramica y servicio premium.', 2, 250000.00, 'img/Grand-Master-interior-cama-2048x1365-1.webp', 'disponible', '2026-04-10 22:32:38'),
(4, '202', 'familiar', 'Habitacion familiar con dos camas dobles, ideal para familias, desayuno incluido.', 4, 180000.00, 'img/Nayara20del20-20Bedroom.webp', 'disponible', '2026-04-10 22:32:38'),
(5, '301', 'sencilla', 'Habitacion sencilla economica, perfecta para viajeros solos.', 1, 70000.00, 'img/images (1).jpg', 'disponible', '2026-04-10 22:32:38'),
(6, '302', 'doble', 'Habitacion doble superior con balcon y vista a la montana.', 2, 150000.00, 'img/images (2).jpg', 'disponible', '2026-04-10 22:32:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL COMMENT 'FK a tabla usuarios',
  `id_habitacion` int(11) NOT NULL COMMENT 'FK a tabla habitaciones',
  `fecha_entrada` date NOT NULL COMMENT 'Fecha de check-in',
  `fecha_salida` date NOT NULL COMMENT 'Fecha de check-out',
  `num_huespedes` int(11) NOT NULL DEFAULT 1 COMMENT 'Numero de huespedes',
  `precio_total` decimal(10,2) NOT NULL COMMENT 'Precio total de la reserva',
  `estado` enum('pendiente','confirmada','cancelada','completada') NOT NULL DEFAULT 'pendiente',
  `notas` text DEFAULT NULL COMMENT 'Notas adicionales del cliente',
  `fecha_reserva` datetime DEFAULT current_timestamp() COMMENT 'Fecha en que se hizo la reserva'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `reservas`
--

-- (sin datos de ejemplo)

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL COMMENT 'Nombres del usuario',
  `apellido` varchar(100) NOT NULL COMMENT 'Apellidos del usuario',
  `tipo_documento` enum('CC','CE','TI','PP') NOT NULL COMMENT 'CC=Cedula, CE=Extranjeria, TI=Tarjeta Identidad, PP=Pasaporte',
  `numero_documento` varchar(20) NOT NULL COMMENT 'Numero de documento de identidad',
  `email` varchar(150) NOT NULL COMMENT 'Correo electronico',
  `username` varchar(50) NOT NULL COMMENT 'Nombre de usuario para login',
  `password` varchar(255) NOT NULL COMMENT 'Contrasena encriptada con password_hash()',
  `telefono` varchar(20) DEFAULT NULL COMMENT 'Numero de telefono',
  `direccion` varchar(255) DEFAULT NULL COMMENT 'Direccion de residencia',
  `genero` enum('M','F','O') NOT NULL COMMENT 'M=Masculino, F=Femenino, O=Otro',
  `rol` enum('administrador','cliente') NOT NULL DEFAULT 'cliente' COMMENT 'Tipo de usuario',
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo' COMMENT 'Estado de la cuenta',
  `fecha_registro` datetime DEFAULT current_timestamp() COMMENT 'Fecha de creacion de cuenta',
  `ultimo_acceso` datetime DEFAULT NULL COMMENT 'Ultima vez que inicio sesion',
  `codigo_recuperacion` varchar(6) DEFAULT NULL,
  `codigo_expira` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

-- (sin datos de ejemplo: registra usuarios desde la pagina)

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `habitaciones`
--
ALTER TABLE `habitaciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_habitacion` (`numero_habitacion`);

--
-- Indices de la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_habitacion` (`id_habitacion`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_documento` (`numero_documento`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `uk_documento` (`tipo_documento`,`numero_documento`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `habitaciones`
--
ALTER TABLE `habitaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservas_ibfk_2` FOREIGN KEY (`id_habitacion`) REFERENCES `habitaciones` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
