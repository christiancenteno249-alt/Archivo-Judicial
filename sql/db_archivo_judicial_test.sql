-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 30-04-2026 a las 19:46:03
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
-- Base de datos: `db_archivo_judicial_test`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria_log`
--

CREATE TABLE `auditoria_log` (
  `id_log` int(11) NOT NULL,
  `fecha_hora` datetime DEFAULT current_timestamp(),
  `id_usuario` int(11) DEFAULT NULL,
  `accion` varchar(50) DEFAULT NULL,
  `recurso` varchar(100) DEFAULT NULL,
  `detalles` text DEFAULT NULL,
  `ip_maquina` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `auditoria_log`
--

INSERT INTO `auditoria_log` (`id_log`, `fecha_hora`, `id_usuario`, `accion`, `recurso`, `detalles`, `ip_maquina`) VALUES
(1, '2026-04-10 13:40:55', 1, 'LOGOUT', 'admin', 'Cierre de sesión', '127.0.0.1'),
(2, '2026-04-10 13:41:28', 1, 'LOGIN', 'admin', 'Inicio de sesión exitoso', '127.0.0.1'),
(3, '2026-04-10 13:42:05', 1, 'LOGOUT', 'admin', 'Cierre de sesión', '127.0.0.1'),
(4, '2026-04-10 13:42:23', 2, 'LOGIN', 'eduardo', 'Inicio de sesión exitoso', '127.0.0.1'),
(5, '2026-04-10 13:46:29', 2, 'CREAR_EXPEDIENTE', 'Exp: 100426', 'Nuevo expediente creado\nTribunal: 69\nDemandante: fran\nDemandado: alex\nLegajo: 369', '127.0.0.1'),
(6, '2026-04-10 13:46:56', 2, 'LOGOUT', 'eduardo', 'Cierre de sesión', '127.0.0.1'),
(7, '2026-04-10 13:46:58', 1, 'LOGIN', 'admin', 'Inicio de sesión exitoso', '127.0.0.1'),
(8, '2026-04-10 13:48:17', 1, 'LOGOUT', 'admin', 'Cierre de sesión', '127.0.0.1'),
(9, '2026-04-10 13:48:27', 2, 'LOGIN', 'eduardo', 'Inicio de sesión exitoso', '127.0.0.1'),
(10, '2026-04-10 13:49:07', 2, 'ACTUALIZAR_EXPEDIENTE', 'Exp: 100426', '[CAMBIO] Tribunal: \'69\' -> \'40\'', '127.0.0.1'),
(11, '2026-04-10 13:49:16', 2, 'LOGOUT', 'eduardo', 'Cierre de sesión', '127.0.0.1'),
(12, '2026-04-10 13:49:19', 1, 'LOGIN', 'admin', 'Inicio de sesión exitoso', '127.0.0.1'),
(13, '2026-04-10 13:53:56', 1, 'LOGOUT', 'admin', 'Cierre de sesión', '127.0.0.1'),
(14, '2026-04-10 13:53:58', 3, 'LOGIN', 'chris', 'Inicio de sesión exitoso', '127.0.0.1'),
(15, '2026-04-10 13:54:27', 3, 'ACTUALIZAR_EXPEDIENTE', 'Exp: 100426', '[CAMBIO] Tribunal: \'40\' -> \'69\'', '127.0.0.1'),
(16, '2026-04-10 13:55:28', 3, 'ACTUALIZAR_EXPEDIENTE', 'Exp: 100426', '[CAMBIO] Estado: \'Archivado\' -> \'Activo\'\n[CAMBIO] N° Legajo: \'369\' -> \'429\'', '127.0.0.1'),
(17, '2026-04-10 13:56:33', 3, 'ACTUALIZAR_EXPEDIENTE', 'Exp: 100426', '[CAMBIO] CI/RIF Demandante: \'V-32443424\' -> \'V-2332424\'\n[CAMBIO] CI/RIF Demandado: \'V-32457890\' -> \'V-29564831\'', '127.0.0.1'),
(18, '2026-04-10 14:22:54', 3, 'LOGOUT', 'chris', 'Cierre de sesión', '127.0.0.1'),
(19, '2026-04-10 14:22:56', 1, 'LOGIN', 'admin', 'Inicio de sesión exitoso', '127.0.0.1'),
(20, '2026-04-10 14:23:19', 1, 'LOGOUT', 'admin', 'Cierre de sesión', '127.0.0.1'),
(21, '2026-04-10 14:23:21', 3, 'LOGIN', 'chris', 'Inicio de sesión exitoso', '127.0.0.1'),
(22, '2026-04-10 14:24:04', 3, 'CREAR_EXPEDIENTE', 'Exp: 00213', 'Nuevo expediente creado\nTribunal: 69\nDemandante: fran\nDemandado: alex\nLegajo: 369', '127.0.0.1'),
(23, '2026-04-10 14:30:33', 3, 'ACTUALIZAR_EXPEDIENTE', 'Exp: 100426', '[CAMBIO] Fecha de Entrada: \'2026-04-10\' -> \'1999-04-10\'\n[CAMBIO] CI/RIF Demandante: \'V-2332424\' -> \'V-4529890\'\n[CAMBIO] CI/RIF Demandado: \'V-29564831\' -> \'V-23564278\'\n[CAMBIO] N° Legajo: \'429\' -> \'369\'', '127.0.0.1'),
(24, '2026-04-10 14:31:35', 3, 'LOGOUT', 'chris', 'Cierre de sesión', '127.0.0.1'),
(25, '2026-04-10 14:31:37', 1, 'LOGIN', 'admin', 'Inicio de sesión exitoso', '127.0.0.1'),
(26, '2026-04-10 14:41:50', 1, 'ACTUALIZAR_EXPEDIENTE', 'Exp: 100426', '[CAMBIO] Estado: \'Activo\' -> \'Archivado\'\n[CAMBIO] Fecha de Entrada: \'1999-04-10\' -> \'2026-04-10\'\n[CAMBIO] CI/RIF Demandante: \'V-4529890\' -> \'V-23545778\'\n[CAMBIO] CI/RIF Demandado: \'V-23564278\' -> \'V-32457890\'', '127.0.0.1'),
(27, '2026-04-10 14:58:35', 1, 'EDITAR_EXPEDIENTE', 'Exp: 100426', '[CAMBIO] Demandante: \'fran\' -> \'fran garcia\'', '127.0.0.1'),
(28, '2026-04-10 15:20:02', 1, 'LOGOUT', 'admin', 'Cierre de sesión', '127.0.0.1'),
(29, '2026-04-13 10:47:11', 1, 'LOGIN', 'admin', 'Inicio de sesión exitoso', '127.0.0.1'),
(30, '2026-04-13 10:56:38', 1, 'RESPALDO_BASE_DATOS', 'Base de Datos Completa (Excel)', 'El Administrador generó una copia de seguridad total del sistema en formato Excel. Archivo: respaldo_total_13_04_2026_165638.xls', '127.0.0.1'),
(31, '2026-04-13 11:15:55', 1, 'RESPALDO_BASE_DATOS', 'Base de Datos Completa (Excel/CSV)', 'El Administrador generó una copia de seguridad total del sistema en formato Excel/CSV. Archivo: respaldo_total_13_04_2026_171555.csv', '127.0.0.1'),
(32, '2026-04-13 11:21:52', 1, 'RESPALDO_BASE_DATOS', 'Base de Datos Completa (SQL)', 'El Administrador generó una copia de seguridad total del sistema en formato SQL. Archivo: respaldo_total_13_04_2026_172152.sql', '127.0.0.1'),
(33, '2026-04-13 11:29:13', 1, 'LOGOUT', 'admin', 'Cierre de sesión', '127.0.0.1'),
(34, '2026-04-13 11:29:19', 2, 'LOGIN', 'eduardo', 'Inicio de sesión exitoso', '127.0.0.1'),
(35, '2026-04-13 11:35:01', 2, 'LOGOUT', 'eduardo', 'Cierre de sesión', '127.0.0.1'),
(36, '2026-04-13 11:35:04', 1, 'LOGIN', 'admin', 'Inicio de sesión exitoso', '127.0.0.1'),
(37, '2026-04-13 11:41:15', 1, 'CREAR_EXPEDIENTE', 'Exp: 4875', 'Nuevo expediente creado\nTribunal: 69\nDemandante: guevara reinaldo\nDemandado: manriquez aura\nLegajo: 358', '127.0.0.1'),
(38, '2026-04-13 11:55:04', 1, 'RESPALDO_BASE_DATOS', 'Base de Datos Completa (SQL)', 'El Administrador generó una copia de seguridad total del sistema en formato SQL. Archivo: respaldo_total_13_04_2026_175504.sql', '127.0.0.1'),
(39, '2026-04-13 11:56:15', 1, 'LOGOUT', 'admin', 'Cierre de sesión', '127.0.0.1'),
(40, '2026-04-13 11:56:19', 2, 'LOGIN', 'eduardo', 'Inicio de sesión exitoso', '127.0.0.1'),
(41, '2026-04-13 12:00:37', 2, 'LOGOUT', 'eduardo', 'Cierre de sesión', '127.0.0.1'),
(42, '2026-04-13 12:00:40', 1, 'LOGIN', 'admin', 'Inicio de sesión exitoso', '127.0.0.1'),
(43, '2026-04-13 12:17:34', 1, 'RESPALDO_BASE_DATOS', 'Base de Datos Completa (Excel/CSV)', 'El Administrador generó una copia de seguridad total del sistema en formato Excel/CSV. Archivo: respaldo_total_13_04_2026_181734.csv', '127.0.0.1'),
(44, '2026-04-13 12:18:35', 1, 'RESPALDO_BASE_DATOS', 'Base de Datos Completa (SQL)', 'El Administrador generó una copia de seguridad total del sistema en formato SQL. Archivo: respaldo_total_13_04_2026_181835.sql', '127.0.0.1'),
(45, '2026-04-14 09:55:49', 1, 'LOGIN', 'admin', 'Inicio de sesión exitoso', '127.0.0.1'),
(46, '2026-04-14 10:37:53', 1, 'EDITAR_EXPEDIENTE', 'Exp: 4875', '[CAMBIO] Tribunal: \'69\' -> \'38\'', '127.0.0.1'),
(47, '2026-04-14 11:04:48', 1, 'EDITAR_EXPEDIENTE', 'Exp: 4875', '[CAMBIO] Tribunal: \'38\' -> \'69\'', '127.0.0.1'),
(48, '2026-04-14 11:04:57', 1, 'EDITAR_EXPEDIENTE', 'Exp: 4875', '[CAMBIO] Tribunal: \'69\' -> \'38\'', '127.0.0.1'),
(49, '2026-04-14 11:15:38', 1, 'EDITAR_EXPEDIENTE', 'Exp: 00213', '[CAMBIO] Demandante: \'fran\' -> \'jose\'', '127.0.0.1'),
(50, '2026-04-14 11:16:27', 1, 'EDITAR_EXPEDIENTE', 'Exp: 00212', '[CAMBIO] N° Expediente: \'00213\' -> \'00212\'', '127.0.0.1'),
(51, '2026-04-14 11:17:35', 1, 'ACTUALIZAR_EXPEDIENTE', 'Exp: 00212', '[CAMBIO] Tribunal: \'69\' -> \'60\'\n[CAMBIO] Estado: \'Activo\' -> \'Archivado\'\n[CAMBIO] Fecha de Entrada: \'2026-04-10\' -> \'2026-04-14\'\n[CAMBIO] Demandante: \'jose\' -> \'pan\'\n[CAMBIO] CI/RIF Demandante: \'V-32423432\' -> \'\'\n[CAMBIO] Demandado: \'alex\' -> \'azucar\'\n[CAMBIO] CI/RIF Demandado: \'V-12343523\' -> \'\'\n[CAMBIO] Motivo/Delito: \'divorcio\' -> \'muerte\'\n[CAMBIO] N° Legajo: \'369\' -> \'475\'', '127.0.0.1'),
(52, '2026-04-14 11:43:04', 1, 'ACTUALIZACION_POR_DUPLICIDAD', 'Exp: 00212', 'Actualización confirmada por el usuario (expediente existente)\n[CAMBIO] Demandante: \'pan\' -> \'cafe\'', '127.0.0.1'),
(53, '2026-04-14 13:58:27', 1, 'ACTUALIZACION_POR_DUPLICIDAD', 'Exp: 00212', 'Actualización confirmada por el usuario (expediente existente)\n[CAMBIO] Estado: \'Archivado\' -> \'Activo\'\n[CAMBIO] Demandante: \'cafe\' -> \'pan\'\n[CAMBIO] Demandado: \'azucar\' -> \'mantequilla\'\n[CAMBIO] Motivo/Delito: \'muerte\' -> \'jamon\'', '127.0.0.1'),
(54, '2026-04-14 14:09:08', 1, 'CREAR_EXPEDIENTE', 'Exp: 0024', 'Nuevo expediente creado\nTribunal: 69\nDemandante: empana\nDemandado: queso\nLegajo: 123', '127.0.0.1'),
(55, '2026-04-14 14:09:42', 1, 'EDITAR_EXPEDIENTE', 'Exp: 0022', '[CAMBIO] N° Expediente: \'0024\' -> \'0022\'', '127.0.0.1'),
(56, '2026-04-14 14:10:29', 1, 'EDITAR_EXPEDIENTE', 'Exp: 0024', '[CAMBIO] N° Expediente: \'0022\' -> \'0024\'', '127.0.0.1'),
(57, '2026-04-14 14:11:59', 1, 'ACTUALIZACION_POR_DUPLICIDAD', 'Exp: 0024', 'Actualización confirmada por el usuario (expediente existente)\n[CAMBIO] Tribunal: \'69\' -> \'39\'', '127.0.0.1'),
(58, '2026-04-14 15:27:26', 1, 'ACTUALIZACION_POR_DUPLICIDAD', 'Exp: 0024', 'Actualización confirmada por el usuario (expediente existente)\n[CAMBIO] Fecha de Entrada: \'2026-04-14\' -> \'2026-04-08\'\n[CAMBIO] Demandante: \'empana\' -> \'pan\'', '127.0.0.1'),
(59, '2026-04-15 08:59:22', 1, 'EDITAR_EXPEDIENTE', 'Exp: 0024', '[CAMBIO] Tribunal: \'39\' -> \'40\'', '127.0.0.1'),
(60, '2026-04-15 09:07:34', 1, 'CREAR_EXPEDIENTE', 'Exp: 0001', 'Nuevo expediente creado\nTribunal: 64\nDemandante: aja\nDemandado: ojo\nLegajo: 456', '127.0.0.1'),
(61, '2026-04-15 09:14:54', 1, 'EDITAR_EXPEDIENTE', 'Exp: 0001', '[CAMBIO] Tribunal: \'64\' -> \'2\'', '127.0.0.1'),
(62, '2026-04-15 09:15:50', 1, 'EDITAR_EXPEDIENTE', 'Exp: 0001', '[CAMBIO] Tribunal: \'2\' -> \'64\'', '127.0.0.1'),
(63, '2026-04-15 09:16:37', 1, 'EDITAR_EXPEDIENTE', 'Exp: 0001', '[CAMBIO] Tribunal: \'64\' -> \'2\'', '127.0.0.1'),
(64, '2026-04-15 09:17:20', 1, 'EDITAR_EXPEDIENTE', 'Exp: 0001', '[CAMBIO] Tribunal: \'2\' -> \'64\'', '127.0.0.1'),
(65, '2026-04-15 09:18:18', 1, 'EDITAR_EXPEDIENTE', 'Exp: 0001', '[CAMBIO] N° Legajo: \'456\' -> \'329\'', '127.0.0.1'),
(66, '2026-04-15 09:29:54', 1, 'EDITAR_EXPEDIENTE', 'Exp: 0001', '[CAMBIO] Tribunal: \'64\' -> \'2\'', '127.0.0.1'),
(67, '2026-04-15 09:30:26', 1, 'EDITAR_EXPEDIENTE', 'Exp: 0001', '[CAMBIO] Tribunal: \'2\' -> \'64\'', '127.0.0.1'),
(68, '2026-04-15 09:31:20', 1, 'EDITAR_EXPEDIENTE', 'Exp: 0024', '[CAMBIO] Tribunal: \'40\' -> \'39\'', '127.0.0.1'),
(69, '2026-04-15 09:42:10', 1, 'CREAR_EXPEDIENTE', 'Exp: 003', 'Nuevo expediente creado\nTribunal: 64\nDemandante: tu\nDemandado: yo\nLegajo: 429', '127.0.0.1'),
(70, '2026-04-15 10:05:32', 1, 'EDITAR_EXPEDIENTE', 'Exp: 01', '[CAMBIO] N° Expediente: \'0024\' -> \'01\'', '127.0.0.1'),
(71, '2026-04-15 10:07:18', 1, 'CREAR_EXPEDIENTE', 'Exp: 02', 'Nuevo expediente creado\nTribunal: 64\nDemandante: tu\nDemandado: yo\nLegajo: 789', '127.0.0.1'),
(72, '2026-04-15 10:24:35', 1, 'LOGOUT', 'admin', 'Cierre de sesión', '127.0.0.1'),
(73, '2026-04-15 10:24:46', 1, 'LOGIN', 'admin', 'Inicio de sesión exitoso', '127.0.0.1'),
(74, '2026-04-15 10:32:28', 1, 'LOGOUT', 'admin', 'Cierre de sesión', '127.0.0.1'),
(75, '2026-04-15 10:32:40', 1, 'LOGIN', 'admin', 'Inicio de sesión exitoso', '127.0.0.1'),
(76, '2026-04-15 10:35:13', 1, 'LOGOUT', 'admin', 'Cierre de sesión', '127.0.0.1'),
(77, '2026-04-15 10:35:18', 2, 'LOGIN', 'eduardo', 'Inicio de sesión exitoso', '127.0.0.1'),
(78, '2026-04-15 10:43:00', 2, 'CREAR_EXPEDIENTE', 'Exp: 36281', 'Nuevo expediente creado\nTribunal: 6\nDemandante: Salazar, Óscar Alberto\nDemandado: Rangel, Azalhea de Jesús\nLegajo: 557', '127.0.0.1'),
(79, '2026-04-15 10:47:29', 2, 'EDITAR_EXPEDIENTE', 'Exp: 36281', '[CAMBIO] Fecha de Entrada: \'0196-06-06\' -> \'0196-06-07\'', '127.0.0.1'),
(80, '2026-04-15 10:49:07', 2, 'EDITAR_EXPEDIENTE', 'Exp: 36281', '[CAMBIO] Fecha de Entrada: \'0196-06-07\' -> \'1996-06-07\'', '127.0.0.1'),
(81, '2026-04-16 08:48:21', 1, 'LOGIN', 'admin', 'Inicio de sesión exitoso', '127.0.0.1'),
(82, '2026-04-16 08:48:24', 1, 'LOGOUT', 'admin', 'Cierre de sesión', '127.0.0.1'),
(83, '2026-04-16 08:48:27', 1, 'LOGIN', 'admin', 'Inicio de sesión exitoso', '127.0.0.1'),
(84, '2026-04-16 09:29:53', 1, 'CREAR_EXPEDIENTE', 'Exp: 004', 'Nuevo expediente creado\nTribunal: 64\nDemandante: tu\nDemandado: yo\nLegajo: 654', '127.0.0.1'),
(85, '2026-04-16 09:30:45', 1, 'EDITAR_EXPEDIENTE', 'Exp: 003', '[CAMBIO] Tribunal: \'6\' -> \'64\'', '127.0.0.1'),
(86, '2026-04-16 09:36:01', 1, 'EDITAR_EXPEDIENTE', 'Exp: 004', '[CAMBIO] Tribunal: \'64\' -> \'9\'', '127.0.0.1'),
(87, '2026-04-17 09:31:50', 1, 'LOGIN', 'admin', 'Inicio de sesión exitoso', '127.0.0.1'),
(88, '2026-04-17 10:00:57', 1, 'CREAR_EXPEDIENTE', 'Exp: 4875', 'Nuevo expediente creado\nTribunal: 64\nDemandante: Salazar, Óscar Alberto\nDemandado: vegas ramon\nLegajo: 557', '127.0.0.1'),
(89, '2026-04-17 10:09:14', 1, 'LOGOUT', 'admin', 'Cierre de sesión', '127.0.0.1'),
(90, '2026-04-17 10:09:33', 1, 'LOGIN', 'admin', 'Inicio de sesión exitoso', '127.0.0.1'),
(91, '2026-04-17 11:25:23', 1, 'LOGOUT', 'admin', 'Cierre de sesión', '127.0.0.1'),
(92, '2026-04-17 11:25:35', 1, 'LOGIN', 'admin', 'Inicio de sesión exitoso', '127.0.0.1'),
(93, '2026-04-17 11:25:37', 1, 'LOGOUT', 'admin', 'Cierre de sesión', '127.0.0.1'),
(94, '2026-04-17 11:25:40', 2, 'LOGIN', 'eduardo', 'Inicio de sesión exitoso', '127.0.0.1'),
(95, '2026-04-17 11:27:42', 2, 'CREAR_EXPEDIENTE', 'Exp: 000-78', 'Nuevo expediente creado\nTribunal: 25\nDemandante: Salazar, Óscar Alberto\nDemandado: Rangel, Azalhea de Jesús\nLegajo: L-776', '127.0.0.1'),
(96, '2026-04-17 11:32:15', 2, 'LOGOUT', 'eduardo', 'Cierre de sesión', '127.0.0.1'),
(97, '2026-04-17 11:32:17', 1, 'LOGIN', 'admin', 'Inicio de sesión exitoso', '127.0.0.1'),
(98, '2026-04-20 09:12:34', 1, 'LOGIN', 'admin', 'Inicio de sesión exitoso', '127.0.0.1'),
(99, '2026-04-20 13:34:24', 1, 'EDITAR_SEDE', 'TRIBUNAL PRIMERO DE MUNICIPIO ORDINARIO Y EJECUTOR DE MEDIDAS DEL MUNICIPIO SANTIAGO MARIÑO DE LA CI', 'Sede actualizada: TRIBUNAL PRIMERO DE MUNICIPIO ORDINARIO Y EJECUTOR DE MEDIDAS DEL MUNICIPIO SANTIAGO MARIÑO DE LA CIRCUNSCRIPCIÓN JUDICIAL DEL ESTADO ARAGUA', '127.0.0.1'),
(100, '2026-04-20 13:35:10', 1, 'EDITAR_SEDE', 'Palo Negro', 'Sede actualizada: Palo Negro', '127.0.0.1'),
(101, '2026-04-20 13:36:13', 1, 'CREAR_SEDE', 'JUZGADO AGRARIO PRIMERO DE PRIMERA INSTANCIA DE LA CIRCUNSCRIPCIÓN JUDICIAL DEL ESTADO ARAGUA', 'Nueva sede creada: JUZGADO AGRARIO PRIMERO DE PRIMERA INSTANCIA DE LA CIRCUNSCRIPCIÓN JUDICIAL DEL ESTADO ARAGUA', '127.0.0.1'),
(102, '2026-04-20 13:38:07', 1, 'CREAR_SEDE', 'JUZGADO DE PRIMERA INSTANCIA EN LO CIVIL, MERCANTIL, TRANSITO Y BANCARIO, DE LA CIRCUNSCRIPCIÓN JUDI', 'Nueva sede creada: JUZGADO DE PRIMERA INSTANCIA EN LO CIVIL, MERCANTIL, TRANSITO Y BANCARIO, DE LA CIRCUNSCRIPCIÓN JUDICIAL DEL ESTADO ARAGUA', '127.0.0.1'),
(103, '2026-04-20 13:39:58', 1, 'EDITAR_SEDE', 'ARCHIVO MUNICIPIOS', 'Sede actualizada: ARCHIVO MUNICIPIOS', '127.0.0.1'),
(104, '2026-04-20 13:42:51', 1, 'LOGOUT', 'admin', 'Cierre de sesión', '127.0.0.1'),
(105, '2026-04-20 13:42:54', 2, 'LOGIN', 'eduardo', 'Inicio de sesión exitoso', '127.0.0.1'),
(106, '2026-04-20 13:43:04', 2, 'LOGOUT', 'eduardo', 'Cierre de sesión', '127.0.0.1'),
(107, '2026-04-20 13:43:11', 1, 'LOGIN', 'admin', 'Inicio de sesión exitoso', '127.0.0.1'),
(108, '2026-04-20 13:51:59', 1, 'CAMBIO_UBICACION_LOTE', 'Exp: 004', 'Cambio de Ubicación (Lote): 004 movido a ARCHIVO MUNICIPIOS - Cuarto de archivos - area B, estante A', '127.0.0.1'),
(109, '2026-04-20 13:59:04', 1, 'CAMBIO_UBICACION', 'Exp: 01', 'Cambio de Ubicación: 01 movido a Galpón Palo Negro - Depósito Central - area B - ESTANTE5 5', '127.0.0.1'),
(110, '2026-04-20 14:01:55', 1, 'CAMBIAR_ESTADO_SEDE', 'Archivo Judicial Maracay Centro', 'Sede desactivada: Archivo Judicial Maracay Centro', '127.0.0.1'),
(111, '2026-04-20 14:02:19', 1, 'CAMBIAR_ESTADO_SEDE', 'Archivo Judicial Maracay Centro', 'Sede activada: Archivo Judicial Maracay Centro', '127.0.0.1'),
(112, '2026-04-20 14:04:34', 1, 'CAMBIAR_ESTADO_SEDE', 'Galpón Palo Negro - Depósito Central', 'Sede desactivada: Galpón Palo Negro - Depósito Central', '127.0.0.1'),
(113, '2026-04-20 14:04:38', 1, 'CAMBIAR_ESTADO_SEDE', 'Depósito Temporal La Victoria', 'Sede desactivada: Depósito Temporal La Victoria', '127.0.0.1'),
(114, '2026-04-20 14:04:49', 1, 'CAMBIAR_ESTADO_SEDE', 'Archivo Judicial Maracay Centro', 'Sede desactivada: Archivo Judicial Maracay Centro', '127.0.0.1'),
(115, '2026-04-20 14:04:55', 1, 'CAMBIAR_ESTADO_SEDE', 'Archivo Judicial Maracay Centro', 'Sede activada: Archivo Judicial Maracay Centro', '127.0.0.1'),
(116, '2026-04-20 14:22:39', 1, 'CAMBIO_UBICACION', 'Exp: 01', 'Cambio de Ubicación: 01 movido a ARCHIVO MUNICIPIOS - area B - ESTANTE5 5', '127.0.0.1'),
(117, '2026-04-20 14:32:04', 1, 'EDITAR_SEDE', 'TRIBUNAL PRIMERO DE MUNICIPIO ORDINARIO Y EJECUTOR DE MEDIDAS DEL MUNICIPIO SANTIAGO MARIÑO DE LA CI', 'Sede actualizada: TRIBUNAL PRIMERO DE MUNICIPIO ORDINARIO Y EJECUTOR DE MEDIDAS DEL MUNICIPIO SANTIAGO MARIÑO DE LA CI', '127.0.0.1'),
(118, '2026-04-20 14:34:24', 1, 'CAMBIO_UBICACION', 'Exp: 000-78', 'Cambio de Ubicación: 000-78 movido a TRIBUNAL PRIMERO DE MUNICIPIO ORDINARIO Y EJECUTOR DE MEDIDAS DEL MUNICIPIO SANTIAGO MARIÑO DE LA CI - area B', '127.0.0.1'),
(119, '2026-04-20 14:51:53', 1, 'EDITAR_SEDE', 'ARCHIVO MUNICIPIOS', 'Sede actualizada: ARCHIVO MUNICIPIOS', '127.0.0.1'),
(120, '2026-04-20 14:52:47', 1, 'EDITAR_SEDE', 'JUZGADO DE PRIMERA INSTANCIA EN LO CIVIL, MERCANTIL, TRANSITO Y BANCARIO, DE LA CIRCUNSCRIPCIÓN JUDI', 'Sede actualizada: JUZGADO DE PRIMERA INSTANCIA EN LO CIVIL, MERCANTIL, TRANSITO Y BANCARIO, DE LA CIRCUNSCRIPCIÓN JUDI', '127.0.0.1'),
(121, '2026-04-20 14:53:31', 1, 'EDITAR_SEDE', 'JUZGADO AGRARIO PRIMERO DE PRIMERA INSTANCIA DE LA CIRCUNSCRIPCIÓN JUDICIAL DEL ESTADO ARAGUA', 'Sede actualizada: JUZGADO AGRARIO PRIMERO DE PRIMERA INSTANCIA DE LA CIRCUNSCRIPCIÓN JUDICIAL DEL ESTADO ARAGUA', '127.0.0.1'),
(122, '2026-04-20 14:53:37', 1, 'EDITAR_SEDE', 'ARCHIVO MUNICIPIOS', 'Sede actualizada: ARCHIVO MUNICIPIOS', '127.0.0.1'),
(123, '2026-04-20 14:54:35', 1, 'EDITAR_SEDE', 'JUZGADO AGRARIO PRIMERO DE PRIMERA INSTANCIA DE LA CIRCUNSCRIPCIÓN JUDICIAL DEL ESTADO ARAGUA', 'Sede actualizada: JUZGADO AGRARIO PRIMERO DE PRIMERA INSTANCIA DE LA CIRCUNSCRIPCIÓN JUDICIAL DEL ESTADO ARAGUA', '127.0.0.1'),
(124, '2026-04-20 14:55:37', 1, 'EDITAR_SEDE', 'JUZGADO DE PRIMERA INSTANCIA EN LO CIVIL, MERCANTIL, TRANSITO Y BANCARIO, DE LA CIRCUNSCRIPCIÓN JUDI', 'Sede actualizada: JUZGADO DE PRIMERA INSTANCIA EN LO CIVIL, MERCANTIL, TRANSITO Y BANCARIO, DE LA CIRCUNSCRIPCIÓN JUDICIAL DEL ESTADO ARAGUA', '127.0.0.1'),
(125, '2026-04-20 14:56:08', 1, 'EDITAR_SEDE', 'JUZGADO DE PRIMERA INSTANCIA EN LO CIVIL, MERCANTIL, TRANSITO Y BANCARIO, DE LA CIRCUNSCRIPCIÓN JUDI', 'Sede actualizada: JUZGADO DE PRIMERA INSTANCIA EN LO CIVIL, MERCANTIL, TRANSITO Y BANCARIO, DE LA CIRCUNSCRIPCIÓN JUDICIAL DEL ESTADO ARAGUA', '127.0.0.1'),
(126, '2026-04-20 14:56:13', 1, 'EDITAR_SEDE', 'JUZGADO DE PRIMERA INSTANCIA EN LO CIVIL, MERCANTIL, TRANSITO Y BANCARIO, DE LA CIRCUNSCRIPCIÓN JUDI', 'Sede actualizada: JUZGADO DE PRIMERA INSTANCIA EN LO CIVIL, MERCANTIL, TRANSITO Y BANCARIO, DE LA CIRCUNSCRIPCIÓN JUDICIAL DEL ESTADO ARAGUA', '127.0.0.1'),
(127, '2026-04-20 14:56:16', 1, 'EDITAR_SEDE', 'JUZGADO DE PRIMERA INSTANCIA EN LO CIVIL, MERCANTIL, TRANSITO Y BANCARIO, DE LA CIRCUNSCRIPCIÓN JUDI', 'Sede actualizada: JUZGADO DE PRIMERA INSTANCIA EN LO CIVIL, MERCANTIL, TRANSITO Y BANCARIO, DE LA CIRCUNSCRIPCIÓN JUDICIAL DEL ESTADO ARAGUA', '127.0.0.1'),
(128, '2026-04-20 14:56:37', 1, 'EDITAR_SEDE', 'JUZGADO DE PRIMERA INSTANCIA EN LO CIVIL, MERCANTIL, TRANSITO Y BANCARIO, DE LA CIRCUNSCRIPCIÓN JUDI', 'Sede actualizada: JUZGADO DE PRIMERA INSTANCIA EN LO CIVIL, MERCANTIL, TRANSITO Y BANCARIO, DE LA CIRCUNSCRIPCIÓN JUDICIAL DEL ESTADO ARAGUA', '127.0.0.1'),
(129, '2026-04-20 15:33:55', 1, 'EDITAR_SEDE', 'JUZGADO DE PRIMERA INSTANCIA EN LO CIVIL, MERCANTIL, TRANSITO Y BANCARIO, DE LA CIRCUNSCRIPCIÓN JUDI', 'Sede actualizada: JUZGADO DE PRIMERA INSTANCIA EN LO CIVIL, MERCANTIL, TRANSITO Y BANCARIO, DE LA CIRCUNSCRIPCIÓN JUDICIAL DEL ESTADO ARAGUA', '127.0.0.1'),
(130, '2026-04-20 16:11:36', 1, 'LOGOUT', 'admin', 'Cierre de sesión', '127.0.0.1'),
(131, '2026-04-20 16:11:39', 2, 'LOGIN', 'eduardo', 'Inicio de sesión exitoso', '127.0.0.1'),
(132, '2026-04-20 16:21:14', 2, 'LOGOUT', 'eduardo', 'Cierre de sesión', '127.0.0.1'),
(133, '2026-04-20 16:21:16', 1, 'LOGIN', 'admin', 'Inicio de sesión exitoso', '127.0.0.1'),
(134, '2026-04-20 16:23:24', 1, 'EDITAR_SEDE', 'ARCHIVO MUNICIPIOS', 'Sede actualizada: ARCHIVO MUNICIPIOS', '127.0.0.1'),
(135, '2026-04-20 16:25:00', 1, 'EDITAR_SEDE', 'TRIBUNAL PRIMERO DE MUNICIPIO ORDINARIO Y EJECUTOR DE MEDIDAS DEL MUNICIPIO SANTIAGO MARIÑO DE LA CI', 'Sede actualizada: TRIBUNAL PRIMERO DE MUNICIPIO ORDINARIO Y EJECUTOR DE MEDIDAS DEL MUNICIPIO SANTIAGO MARIÑO DE LA CI', '127.0.0.1'),
(136, '2026-04-20 16:26:20', 1, 'CAMBIO_UBICACION_LOTE', 'Exp: 01', 'Cambio de Ubicación (Lote): 01 movido a TRIBUNAL PRIMERO DE MUNICIPIO ORDINARIO Y EJECUTOR DE MEDIDAS DEL MUNICIPIO SANTIAGO MARIÑO DE LA CI - Cuarto de archivos - area B, estante A', '127.0.0.1'),
(137, '2026-04-20 16:26:20', 1, 'CAMBIO_UBICACION_LOTE', 'Exp: 000-78', 'Cambio de Ubicación (Lote): 000-78 movido a TRIBUNAL PRIMERO DE MUNICIPIO ORDINARIO Y EJECUTOR DE MEDIDAS DEL MUNICIPIO SANTIAGO MARIÑO DE LA CI - Cuarto de archivos - area B, estante A', '127.0.0.1'),
(138, '2026-04-21 08:51:44', 1, 'LOGIN', 'admin', 'Inicio de sesión exitoso', '127.0.0.1'),
(139, '2026-04-21 09:10:18', 1, 'EDITAR_SEDE', 'tribunal primero de municipio ordinario y ejecutor de medidas del municipio Santiago Mariño de la ci', 'Sede actualizada: tribunal primero de municipio ordinario y ejecutor de medidas del municipio Santiago Mariño de la circusncripcion judicial del estado aragua', '127.0.0.1'),
(140, '2026-04-21 09:10:29', 1, 'EDITAR_SEDE', 'tribunal primero de municipio ordinario y ejecutor de medidas del municipio Santiago Mariño de la ci', 'Sede actualizada: tribunal primero de municipio ordinario y ejecutor de medidas del municipio Santiago Mariño de la circusncripcion judicial del estado aragua', '127.0.0.1'),
(141, '2026-04-21 09:11:10', 1, 'EDITAR_SEDE', 'tribunal primero de municipio ordinario y ejecutor de medidas del municipio Santiago Mariño de la ci', 'Sede actualizada: tribunal primero de municipio ordinario y ejecutor de medidas del municipio Santiago Mariño de la circusncripcion judicial del estado aragua', '127.0.0.1'),
(142, '2026-04-21 09:32:43', 1, 'EDITAR_SEDE', 'Archivo Municipio', 'Sede actualizada: Archivo Municipio', '127.0.0.1'),
(143, '2026-04-21 09:40:16', 1, 'EDITAR_SEDE', 'Juzgado agrario primero de primera instancia de la circunscripcion judicial del estado Aragua', 'Sede actualizada: Juzgado agrario primero de primera instancia de la circunscripcion judicial del estado Aragua', '127.0.0.1'),
(144, '2026-04-21 09:47:57', 1, 'EDITAR_SEDE', 'Juzgado de primera instancia en lo civil, mercantil, transito y bancario, de la circunscripcion judi', 'Sede actualizada: Juzgado de primera instancia en lo civil, mercantil, transito y bancario, de la circunscripcion judicial del estado aragua', '127.0.0.1'),
(145, '2026-04-21 09:48:14', 1, 'EDITAR_SEDE', 'Palo Negro', 'Sede actualizada: Palo Negro', '127.0.0.1'),
(146, '2026-04-21 10:28:43', 1, 'LOGOUT', 'admin', 'Cierre de sesión', '127.0.0.1'),
(147, '2026-04-21 10:28:46', 2, 'LOGIN', 'eduardo', 'Inicio de sesión exitoso', '127.0.0.1'),
(148, '2026-04-21 10:35:28', 2, 'LOGOUT', 'eduardo', 'Cierre de sesión', '127.0.0.1'),
(149, '2026-04-21 10:35:30', 1, 'LOGIN', 'admin', 'Inicio de sesión exitoso', '127.0.0.1'),
(150, '2026-04-21 10:39:52', 1, 'CAMBIO_UBICACION_LOTE', 'Exp: 4875', 'Cambio de Ubicación (Lote): 4875 movido a tribunal primero de municipio ordinario y ejecutor de medidas del municipio Santiago Mariño de la circusncripcion judicial del estado aragua - Cuarto de archivos - Estante A', '127.0.0.1'),
(151, '2026-04-21 10:39:52', 1, 'CAMBIO_UBICACION_LOTE', 'Exp: 003', 'Cambio de Ubicación (Lote): 003 movido a tribunal primero de municipio ordinario y ejecutor de medidas del municipio Santiago Mariño de la circusncripcion judicial del estado aragua - Cuarto de archivos - Estante A', '127.0.0.1'),
(152, '2026-04-21 10:41:11', 1, 'LOGOUT', 'admin', 'Cierre de sesión', '127.0.0.1'),
(153, '2026-04-21 10:41:14', 2, 'LOGIN', 'eduardo', 'Inicio de sesión exitoso', '127.0.0.1'),
(154, '2026-04-21 10:44:50', 2, 'CREAR_EXPEDIENTE', 'Exp: 5432', 'Nuevo expediente creado\nTribunal: 60\nDemandante: Jesus Perez\nDemandado: Santiago Perez\nLegajo: 234', '127.0.0.1'),
(155, '2026-04-21 10:47:35', 2, 'CAMBIO_UBICACION_LOTE', 'Exp: 5432', 'Cambio de Ubicación (Lote): 5432 movido a Juzgado de primera instancia en lo civil, mercantil, transito y bancario, de la circunscripcion judicial del estado aragua - Cuarto de archivos - Estante D', '127.0.0.1'),
(156, '2026-04-21 10:48:23', 2, 'LOGOUT', 'eduardo', 'Cierre de sesión', '127.0.0.1'),
(157, '2026-04-21 10:48:26', 1, 'LOGIN', 'admin', 'Inicio de sesión exitoso', '127.0.0.1'),
(158, '2026-04-21 21:51:16', 1, 'LOGIN', 'admin', 'Inicio de sesión exitoso', '127.0.0.1'),
(159, '2026-04-21 21:53:20', 1, 'LOGOUT', 'admin', 'Cierre de sesión', '127.0.0.1'),
(160, '2026-04-22 09:57:38', 1, 'LOGIN', 'admin', 'Inicio de sesión exitoso', '127.0.0.1'),
(161, '2026-04-23 08:59:22', 1, 'LOGIN', 'admin', 'Inicio de sesión exitoso', '127.0.0.1'),
(162, '2026-04-23 10:10:11', 1, 'LOGOUT', 'admin', 'Cierre de sesion', '127.0.0.1'),
(163, '2026-04-23 10:10:16', 1, 'LOGIN', 'admin', 'Inicio de sesion exitoso', '127.0.0.1'),
(164, '2026-04-23 10:23:41', 1, 'LOGOUT', 'admin', 'Cierre de sesion', '127.0.0.1'),
(165, '2026-04-23 10:23:43', 1, 'LOGIN', 'admin', 'Inicio de sesion exitoso', '127.0.0.1'),
(166, '2026-04-23 10:52:11', 1, 'LOGOUT', 'admin', 'Cierre de sesion', '127.0.0.1'),
(167, '2026-04-23 10:53:10', 1, 'LOGIN', 'admin', 'Inicio de sesion exitoso', '127.0.0.1'),
(168, '2026-04-23 11:45:01', 1, 'LOGOUT', 'admin', 'Cierre de sesion', '127.0.0.1'),
(169, '2026-04-23 11:45:06', 1, 'LOGIN', 'admin', 'Inicio de sesion exitoso', '127.0.0.1'),
(170, '2026-04-23 12:03:48', 1, 'CAMBIO_UBICACION', 'Exp: 000-78', 'Cambio de Ubicacion: 000-78 movido a tribunal primero de municipio ordinario y ejecutor de medidas del municipio Santiago Mariño de la circusncripcion judicial del estado aragua - Cuarto de archivos - area B, estante A', '127.0.0.1'),
(171, '2026-04-24 09:23:57', 1, 'LOGIN', 'admin', 'Inicio de sesion exitoso', '127.0.0.1'),
(172, '2026-04-24 10:09:37', 1, 'CREAR_EXPEDIENTE', 'Exp: 100426', 'Nuevo expediente creado\nTribunal: 39\nDemandante: Salazar, Óscar Alberto\nDemandado: vegas ramon\nLegajo: 789', '127.0.0.1'),
(173, '2026-04-24 10:10:46', 1, 'CREAR_EXPEDIENTE', 'Exp: 0024', 'Nuevo expediente creado\nTribunal: 38\nDemandante: guevara reinaldo\nDemandado: manriquez aura\nLegajo: 369', '127.0.0.1'),
(174, '2026-04-24 10:11:10', 1, 'CREAR_EXPEDIENTE', 'Exp: 0001', 'Nuevo expediente creado\nTribunal: 38\nDemandante: aja\nDemandado: manriquez aura\nLegajo: 369', '127.0.0.1'),
(175, '2026-04-24 10:11:11', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 0001', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(176, '2026-04-24 10:11:11', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 0001', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(177, '2026-04-24 10:11:11', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 0001', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(178, '2026-04-24 10:11:11', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 0001', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(179, '2026-04-24 10:11:11', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 0001', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(180, '2026-04-24 10:11:11', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 0001', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(181, '2026-04-24 10:11:11', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 0001', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(182, '2026-04-24 10:11:54', 1, 'CREAR_EXPEDIENTE', 'Exp: 3432', 'Nuevo expediente creado\nTribunal: 38\nDemandante: guevara reinaldo\nDemandado: alex\nLegajo: 358', '127.0.0.1'),
(183, '2026-04-24 10:11:55', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(184, '2026-04-24 10:11:55', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(185, '2026-04-24 10:11:56', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(186, '2026-04-24 10:11:56', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(187, '2026-04-24 10:11:56', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(188, '2026-04-24 10:11:57', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(189, '2026-04-24 10:11:57', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(190, '2026-04-24 10:11:57', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(191, '2026-04-24 10:11:58', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(192, '2026-04-24 10:11:58', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(193, '2026-04-24 10:11:58', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(194, '2026-04-24 10:11:59', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(195, '2026-04-24 10:11:59', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(196, '2026-04-24 10:11:59', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(197, '2026-04-24 10:11:59', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(198, '2026-04-24 10:12:00', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(199, '2026-04-24 10:12:00', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(200, '2026-04-24 10:12:00', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(201, '2026-04-24 10:12:01', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(202, '2026-04-24 10:12:01', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(203, '2026-04-24 10:12:01', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(204, '2026-04-24 10:12:02', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(205, '2026-04-24 10:12:02', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(206, '2026-04-24 10:12:02', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(207, '2026-04-24 10:12:03', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(208, '2026-04-24 10:12:03', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(209, '2026-04-24 10:12:03', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(210, '2026-04-24 10:12:04', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(211, '2026-04-24 10:12:04', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(212, '2026-04-24 10:12:04', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(213, '2026-04-24 10:12:04', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(214, '2026-04-24 10:12:04', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(215, '2026-04-24 10:12:04', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(216, '2026-04-24 10:12:04', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(217, '2026-04-24 10:12:04', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(218, '2026-04-24 10:12:04', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(219, '2026-04-24 10:12:05', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(220, '2026-04-24 10:12:05', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(221, '2026-04-24 10:12:05', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(222, '2026-04-24 10:12:05', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(223, '2026-04-24 10:12:05', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(224, '2026-04-24 10:12:05', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(225, '2026-04-24 10:12:05', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(226, '2026-04-24 10:12:06', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(227, '2026-04-24 10:12:06', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(228, '2026-04-24 10:12:06', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(229, '2026-04-24 10:12:06', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(230, '2026-04-24 10:12:06', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(231, '2026-04-24 10:12:06', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(232, '2026-04-24 10:12:06', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(233, '2026-04-24 10:12:07', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(234, '2026-04-24 10:12:07', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(235, '2026-04-24 10:12:07', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(236, '2026-04-24 10:12:07', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(237, '2026-04-24 10:12:07', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(238, '2026-04-24 10:12:07', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(239, '2026-04-24 10:12:07', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(240, '2026-04-24 10:12:08', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(241, '2026-04-24 10:12:08', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(242, '2026-04-24 10:12:08', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(243, '2026-04-24 10:12:08', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(244, '2026-04-24 10:12:08', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(245, '2026-04-24 10:12:08', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(246, '2026-04-24 10:12:10', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(247, '2026-04-24 10:12:13', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 3432', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(248, '2026-04-27 09:18:07', 1, 'LOGIN', 'admin', 'Inicio de sesion exitoso', '127.0.0.1'),
(249, '2026-04-27 14:38:51', 1, 'LOGIN', 'admin', 'Inicio de sesion exitoso', '127.0.0.1'),
(250, '2026-04-28 09:05:03', 1, 'LOGIN', 'admin', 'Inicio de sesion exitoso', '127.0.0.1'),
(251, '2026-04-28 09:18:59', 1, 'CREAR_EXPEDIENTE', 'Exp: 0000', 'Nuevo expediente creado\nTribunal: 29\nDemandante: pan\nDemandado: dadi feer\nLegajo: 789', '127.0.0.1'),
(252, '2026-04-28 10:27:46', 1, 'LOGIN', 'admin', 'Inicio de sesion exitoso', '127.0.0.1'),
(253, '2026-04-28 10:32:31', 1, 'CREAR_EXPEDIENTE', 'Exp: 0006', 'Nuevo expediente creado\nTribunal: 14\nDemandante: pan\nDemandado: alex\nLegajo: 0004', '127.0.0.1'),
(254, '2026-04-28 10:32:33', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 0006', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(255, '2026-04-28 10:34:04', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 0006', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(256, '2026-04-28 10:34:16', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 0006', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(257, '2026-04-28 10:35:49', 1, 'CREAR_EXPEDIENTE', 'Exp: 2026', 'Nuevo expediente creado\nTribunal: 40\nDemandante: pan\nDemandado: ojo\nLegajo: 4565', '127.0.0.1'),
(258, '2026-04-28 10:43:39', 1, 'CREAR_EXPEDIENTE', 'Exp: 897987', 'Nuevo expediente creado\nTribunal: 38\nDemandante: francis\nDemandado: delgadi\nLegajo: 5578', '127.0.0.1'),
(259, '2026-04-28 10:47:09', 1, 'CREAR_EXPEDIENTE', 'Exp: 342424423', 'Nuevo expediente creado\nTribunal: 60\nDemandante: dawdadawaw\nDemandado: ddacvrerwrr\nLegajo: 557', '127.0.0.1'),
(260, '2026-04-28 10:49:15', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 342424423', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(261, '2026-04-28 10:53:02', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 342424423', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(262, '2026-04-28 10:53:29', 1, 'CREAR_EXPEDIENTE', 'Exp: 100426323', 'Nuevo expediente creado\nTribunal: 39\nDemandante: aja32332\nDemandado: 113dd3d3\nLegajo: de43434', '127.0.0.1'),
(263, '2026-04-28 10:57:42', 1, 'CREAR_EXPEDIENTE', 'Exp: 10042623', 'Nuevo expediente creado\nTribunal: 69\nDemandante: Salazar, Óscar Alberto\nDemandado: ojo\nLegajo: 23232', '127.0.0.1'),
(264, '2026-04-28 10:58:01', 1, 'SOBREESCRITURA_POR_DUPLICADO', 'Exp: 10042623', 'Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.', '127.0.0.1'),
(265, '2026-04-28 10:58:17', 1, 'CREAR_EXPEDIENTE', 'Exp: 00212323', 'Nuevo expediente creado\nTribunal: 60\nDemandante: Salazar, Óscar Alberto\nDemandado: 323\nLegajo: 369', '127.0.0.1'),
(266, '2026-04-28 11:02:39', 1, 'CREAR_EXPEDIENTE', 'Exp: 487534', 'Nuevo expediente creado\nTribunal: 27\nDemandante: Salazar, Óscar Alberto\nDemandado: vegas ramon\nLegajo: 358', '127.0.0.1'),
(267, '2026-04-28 11:03:41', 1, 'CREAR_EXPEDIENTE', 'Exp: 00012332', 'Nuevo expediente creado\nTribunal: 38\nDemandante: fran\nDemandado: ojo\nLegajo: 23322323', '127.0.0.1'),
(268, '2026-04-28 13:49:25', 1, 'LOGIN', 'admin', 'Inicio de sesion exitoso', '127.0.0.1'),
(269, '2026-04-28 14:00:17', 1, 'CAMBIO_UBICACION_LOTE', 'Exp: 10042623', 'Cambio de Ubicación (Lote): 10042623 movido a Juzgado agrario primero de primera instancia de la circunscripcion judicial del estado Aragua - cuarto de archivos - area B, estante A', '127.0.0.1'),
(270, '2026-04-28 14:00:17', 1, 'CAMBIO_UBICACION_LOTE', 'Exp: 487534', 'Cambio de Ubicación (Lote): 487534 movido a Juzgado agrario primero de primera instancia de la circunscripcion judicial del estado Aragua - cuarto de archivos - area B, estante A', '127.0.0.1'),
(271, '2026-04-28 14:00:17', 1, 'CAMBIO_UBICACION_LOTE', 'Exp: 00012332', 'Cambio de Ubicación (Lote): 00012332 movido a Juzgado agrario primero de primera instancia de la circunscripcion judicial del estado Aragua - cuarto de archivos - area B, estante A', '127.0.0.1'),
(272, '2026-04-29 13:55:44', 1, 'LOGIN', 'admin', 'Inicio de sesion exitoso', '127.0.0.1'),
(273, '2026-04-29 14:09:50', NULL, 'INTENTO_FALLIDO', 'admin', 'Intento de login con credenciales incorrectas o usuario inactivo', '127.0.0.1'),
(274, '2026-04-29 14:10:20', NULL, 'INTENTO_FALLIDO', 'admin', 'Intento de login con credenciales incorrectas o usuario inactivo', '127.0.0.1'),
(275, '2026-04-29 14:10:41', 1, 'LOGIN', 'admin', 'Inicio de sesion exitoso', '127.0.0.1'),
(276, '2026-04-30 13:33:18', 1, 'LOGIN', 'admin', 'Inicio de sesion exitoso', '127.0.0.1'),
(277, '2026-04-30 13:34:06', 1, 'RESPALDO_BASE_DATOS', 'Base de Datos Completa (Excel/CSV)', 'El Administrador genero una copia de seguridad total del sistema en formato Excel/CSV. Archivo: respaldo_total_30_04_2026_193406.csv', '127.0.0.1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_movimientos`
--

CREATE TABLE `historial_movimientos` (
  `id_movimiento` int(11) NOT NULL,
  `n_expediente` varchar(255) NOT NULL,
  `id_tribunal` int(11) NOT NULL,
  `fecha_movimiento` datetime DEFAULT current_timestamp(),
  `observaciones` text DEFAULT NULL,
  `id_usuario` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `historial_movimientos`
--

INSERT INTO `historial_movimientos` (`id_movimiento`, `n_expediente`, `id_tribunal`, `fecha_movimiento`, `observaciones`, `id_usuario`) VALUES
(13, '01', 69, '2026-04-14 14:09:08', '', 1),
(14, '01', 39, '2026-04-14 14:11:59', '', 1),
(15, '01', 39, '2026-04-14 15:27:26', '', 1),
(18, '02', 64, '2026-04-15 10:07:18', '', 1),
(19, '02', 6, '2026-04-15 10:43:00', '', 2),
(20, '004', 64, '2026-04-16 09:29:53', '', 1),
(21, '4875', 64, '2026-04-17 10:00:57', '', 1),
(22, '000-78', 25, '2026-04-17 11:27:42', '', 2),
(23, '5432', 60, '2026-04-21 10:44:50', '', 2),
(24, '100426', 39, '2026-04-24 10:09:37', '', 1),
(25, '0024', 38, '2026-04-24 10:10:46', '', 1),
(26, '0001', 38, '2026-04-24 10:11:10', '', 1),
(27, '0001', 38, '2026-04-24 10:11:11', '', 1),
(28, '0001', 38, '2026-04-24 10:11:11', '', 1),
(29, '0001', 38, '2026-04-24 10:11:11', '', 1),
(30, '0001', 38, '2026-04-24 10:11:11', '', 1),
(31, '0001', 38, '2026-04-24 10:11:11', '', 1),
(32, '0001', 38, '2026-04-24 10:11:11', '', 1),
(33, '0001', 38, '2026-04-24 10:11:11', '', 1),
(34, '3432', 38, '2026-04-24 10:11:54', '', 1),
(35, '3432', 38, '2026-04-24 10:11:55', '', 1),
(36, '3432', 38, '2026-04-24 10:11:55', '', 1),
(37, '3432', 38, '2026-04-24 10:11:56', '', 1),
(38, '3432', 38, '2026-04-24 10:11:56', '', 1),
(39, '3432', 38, '2026-04-24 10:11:56', '', 1),
(40, '3432', 38, '2026-04-24 10:11:57', '', 1),
(41, '3432', 38, '2026-04-24 10:11:57', '', 1),
(42, '3432', 38, '2026-04-24 10:11:57', '', 1),
(43, '3432', 38, '2026-04-24 10:11:58', '', 1),
(44, '3432', 38, '2026-04-24 10:11:58', '', 1),
(45, '3432', 38, '2026-04-24 10:11:58', '', 1),
(46, '3432', 38, '2026-04-24 10:11:59', '', 1),
(47, '3432', 38, '2026-04-24 10:11:59', '', 1),
(48, '3432', 38, '2026-04-24 10:11:59', '', 1),
(49, '3432', 38, '2026-04-24 10:11:59', '', 1),
(50, '3432', 38, '2026-04-24 10:12:00', '', 1),
(51, '3432', 38, '2026-04-24 10:12:00', '', 1),
(52, '3432', 38, '2026-04-24 10:12:00', '', 1),
(53, '3432', 38, '2026-04-24 10:12:01', '', 1),
(54, '3432', 38, '2026-04-24 10:12:01', '', 1),
(55, '3432', 38, '2026-04-24 10:12:01', '', 1),
(56, '3432', 38, '2026-04-24 10:12:02', '', 1),
(57, '3432', 38, '2026-04-24 10:12:02', '', 1),
(58, '3432', 38, '2026-04-24 10:12:02', '', 1),
(59, '3432', 38, '2026-04-24 10:12:03', '', 1),
(60, '3432', 38, '2026-04-24 10:12:03', '', 1),
(61, '3432', 38, '2026-04-24 10:12:03', '', 1),
(62, '3432', 38, '2026-04-24 10:12:04', '', 1),
(63, '3432', 38, '2026-04-24 10:12:04', '', 1),
(64, '3432', 38, '2026-04-24 10:12:04', '', 1),
(65, '3432', 38, '2026-04-24 10:12:04', '', 1),
(66, '3432', 38, '2026-04-24 10:12:04', '', 1),
(67, '3432', 38, '2026-04-24 10:12:04', '', 1),
(68, '3432', 38, '2026-04-24 10:12:04', '', 1),
(69, '3432', 38, '2026-04-24 10:12:04', '', 1),
(70, '3432', 38, '2026-04-24 10:12:04', '', 1),
(71, '3432', 38, '2026-04-24 10:12:05', '', 1),
(72, '3432', 38, '2026-04-24 10:12:05', '', 1),
(73, '3432', 38, '2026-04-24 10:12:05', '', 1),
(74, '3432', 38, '2026-04-24 10:12:05', '', 1),
(75, '3432', 38, '2026-04-24 10:12:05', '', 1),
(76, '3432', 38, '2026-04-24 10:12:05', '', 1),
(77, '3432', 38, '2026-04-24 10:12:05', '', 1),
(78, '3432', 38, '2026-04-24 10:12:06', '', 1),
(79, '3432', 38, '2026-04-24 10:12:06', '', 1),
(80, '3432', 38, '2026-04-24 10:12:06', '', 1),
(81, '3432', 38, '2026-04-24 10:12:06', '', 1),
(82, '3432', 38, '2026-04-24 10:12:06', '', 1),
(83, '3432', 38, '2026-04-24 10:12:06', '', 1),
(84, '3432', 38, '2026-04-24 10:12:06', '', 1),
(85, '3432', 38, '2026-04-24 10:12:07', '', 1),
(86, '3432', 38, '2026-04-24 10:12:07', '', 1),
(87, '3432', 38, '2026-04-24 10:12:07', '', 1),
(88, '3432', 38, '2026-04-24 10:12:07', '', 1),
(89, '3432', 38, '2026-04-24 10:12:07', '', 1),
(90, '3432', 38, '2026-04-24 10:12:07', '', 1),
(91, '3432', 38, '2026-04-24 10:12:07', '', 1),
(92, '3432', 38, '2026-04-24 10:12:08', '', 1),
(93, '3432', 38, '2026-04-24 10:12:08', '', 1),
(94, '3432', 38, '2026-04-24 10:12:08', '', 1),
(95, '3432', 38, '2026-04-24 10:12:08', '', 1),
(96, '3432', 38, '2026-04-24 10:12:08', '', 1),
(97, '3432', 38, '2026-04-24 10:12:08', '', 1),
(98, '3432', 38, '2026-04-24 10:12:10', '', 1),
(99, '3432', 38, '2026-04-24 10:12:13', '', 1),
(100, '0000', 29, '2026-04-28 09:18:59', '', 1),
(101, '0006', 14, '2026-04-28 10:32:31', '', 1),
(102, '0006', 14, '2026-04-28 10:32:33', '', 1),
(103, '0006', 14, '2026-04-28 10:34:04', '', 1),
(104, '0006', 14, '2026-04-28 10:34:16', '', 1),
(105, '2026', 40, '2026-04-28 10:35:49', '', 1),
(106, '897987', 38, '2026-04-28 10:43:39', '', 1),
(107, '342424423', 60, '2026-04-28 10:47:09', '', 1),
(108, '342424423', 60, '2026-04-28 10:49:15', '', 1),
(109, '342424423', 60, '2026-04-28 10:53:02', '', 1),
(110, '100426323', 39, '2026-04-28 10:53:28', '', 1),
(111, '10042623', 69, '2026-04-28 10:57:42', '', 1),
(112, '10042623', 69, '2026-04-28 10:58:01', '', 1),
(113, '00212323', 60, '2026-04-28 10:58:17', '', 1),
(115, '00012332', 38, '2026-04-28 11:03:41', '', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `maestro`
--

CREATE TABLE `maestro` (
  `Id` int(11) NOT NULL,
  `n_expediente` varchar(255) DEFAULT NULL,
  `fecha_entrada` varchar(100) DEFAULT NULL,
  `fecha_sentencia` varchar(100) DEFAULT NULL,
  `n_legajo` varchar(100) DEFAULT NULL,
  `demandante` longtext DEFAULT NULL,
  `cedula_rif_demandante` varchar(100) DEFAULT NULL,
  `demandado` longtext DEFAULT NULL,
  `cedula_rif_demandado` varchar(100) DEFAULT NULL,
  `desicion` longtext DEFAULT NULL,
  `observaciones` longtext DEFAULT NULL,
  `motivo_delito` text DEFAULT NULL,
  `id_tribunal` int(11) DEFAULT NULL,
  `id_sede` int(11) DEFAULT NULL,
  `ubicacion_area` varchar(100) DEFAULT NULL,
  `ubicacion_detalle` varchar(255) DEFAULT NULL,
  `fecha_ultima_ubicacion` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `maestro`
--

INSERT INTO `maestro` (`Id`, `n_expediente`, `fecha_entrada`, `fecha_sentencia`, `n_legajo`, `demandante`, `cedula_rif_demandante`, `demandado`, `cedula_rif_demandado`, `desicion`, `observaciones`, `motivo_delito`, `id_tribunal`, `id_sede`, `ubicacion_area`, `ubicacion_detalle`, `fecha_ultima_ubicacion`) VALUES
(1, '01', '2026-04-08', NULL, '123', 'pan', '', 'queso', '', NULL, '', 'divorcio', 39, 3, 'Cuarto de archivos', 'area B, estante A', '2026-04-20 20:26:20'),
(2, '02', '2026-04-15', NULL, '789', 'pan', '', 'queso', '', NULL, '', 'divorcio', 64, NULL, NULL, NULL, NULL),
(3, '003', '1996-06-07', NULL, '557', 'pan', '', 'queso', '', NULL, '', 'Separación de Cuerpos', 64, 3, 'Cuarto de archivos', 'Estante A', '2026-04-21 14:39:52'),
(4, '004', '2026-04-17', NULL, '654', 'pan', '', 'queso', '', NULL, '', 'Separación de Cuerpos', 9, 2, 'Cuarto de archivos', 'area B, estante A', '2026-04-20 17:51:59'),
(5, '4875', '2026-04-18', NULL, '557', 'pan ', 'V-2332424', 'queso ', 'V-12343523', NULL, '', 'Separación de Cuerpos', 64, 3, 'Cuarto de archivos', 'Estante A', '2026-04-21 14:39:52'),
(6, '000-78', '2026-04-19', NULL, 'L-776', 'pan', '', 'queso', '', NULL, '', 'Separación de Cuerpos', 25, 3, 'Cuarto de archivos', 'area B, estante A', '2026-04-23 16:03:48'),
(7, '5432', '2026-04-21', NULL, '234', 'pan', '', 'queso', '', NULL, '', 'divorcio', 0, 5, 'Cuarto de archivos', 'Estante D', '2026-04-21 14:47:35'),
(8, '100426', '2026-04-30', NULL, '789', 'pan', 'V-2332424', 'vegas ramon', 'V-29564831', NULL, '', 'Separación de Cuerpos', 39, 4, 'cuarto de archivos', 'Estante D', '2026-04-28 16:07:28'),
(9, '0024', '2026-04-12', NULL, '369', 'pan', 'V-2332424', 'manriquez aura', '', NULL, '', 'divorcio', 38, NULL, NULL, NULL, NULL),
(10, '0001', '2026-04-16', NULL, '369', 'pan', 'V-32423432', 'manriquez aura', 'V-32457890', NULL, '', 'divorcio', 38, NULL, NULL, NULL, NULL),
(11, '3432', '2026-04-09', NULL, '358', 'pan', '', 'alex', '', NULL, '', 'divorcio', 38, NULL, NULL, NULL, NULL),
(12, '0000', '2026-04-28', NULL, '789', 'pan', '', 'dadi feer', '', NULL, '', 'divorcio', 29, 4, 'cuarto de archivos', 'Estante D', '2026-04-28 16:07:28'),
(13, '0006', '2026-04-28', NULL, '0004', 'pan', 'V-298802200', 'alex', 'V-456789870', NULL, '', 'robo', 14, NULL, NULL, NULL, NULL),
(14, '2026', '2026-04-28', NULL, '4565', 'pan', 'V-29890330', 'ojo', 'V-29890330', NULL, '', 'robo', 40, NULL, NULL, NULL, NULL),
(15, '897987', '2026-04-11', NULL, '5578', 'francis', 'V-452989087', 'delgadi', 'V-878987987', NULL, '', 'divorcio', 38, 4, 'cuarto de archivos', 'Estante D', '2026-04-28 16:07:28'),
(16, '342424423', '2026-05-01', NULL, '557', 'dawdadawaw', 'V-423454534', 'ddacvrerwrr', 'V-332432432', NULL, '', 'divorcio', 60, 3, '', '', '2026-04-28 15:49:00'),
(17, '100426323', '2026-04-08', NULL, 'de43434', 'aja32332', 'V-323321332', '113dd3d3', 'V-321333123', NULL, '', 'divorcio', 39, NULL, NULL, NULL, NULL),
(18, '10042623', '2026-04-17', NULL, '23232', 'Salazar, Óscar Alberto', 'V-452989023', 'ojo', 'V-29564831', NULL, '', 'robo', 69, 4, 'cuarto de archivos', 'area B, estante A', '2026-04-28 18:00:17'),
(19, '00212323', '2026-04-30', NULL, '369', 'Salazar, Óscar Alberto', '', '323', '', NULL, '', 'robo', 60, 4, 'cuarto de archivos', 'Estante D', '2026-04-28 16:07:28'),
(21, '00012332', '2026-04-24', NULL, '23322323', 'fran', '', 'ojo', '', NULL, '', 'robo', 38, 4, 'cuarto de archivos', 'area B, estante A', '2026-04-28 18:00:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sedes_deposito`
--

CREATE TABLE `sedes_deposito` (
  `id_sede` int(11) NOT NULL,
  `nombre_sede` varchar(255) NOT NULL,
  `direccion` text DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sedes_deposito`
--

INSERT INTO `sedes_deposito` (`id_sede`, `nombre_sede`, `direccion`, `descripcion`, `activo`, `fecha_creacion`) VALUES
(1, 'Palo Negro', 'Palo Negro, Edo.Aragua, Municipio libertador', '', 1, '2026-04-20 17:23:01'),
(2, 'Archivo Municipio', 'Edificio Tribunales, Maracay Vargas Norte N.º35, antiguo edificio de tribunales penales, piso N.º1, Maracay', 'Telefono: (0414) 094.74.02', 1, '2026-04-20 17:23:01'),
(3, 'tribunal primero de municipio ordinario y ejecutor de medidas del municipio Santiago Mariño de la circusncripcion judicial del estado aragua', 'Calle rivas cruce con Miranda, centro comercial \r\nprofesional Emanuel Randazzo, 3er.nivel locales 20 y 21, turmero', 'telefono: (0244) 663.50.22\r\nCorreo:tribunal1municip.marino.aragua@gmail.com', 1, '2026-04-20 17:23:01'),
(4, 'Juzgado agrario primero de primera instancia de la circunscripcion judicial del estado Aragua', 'Carretera vieja Turmero la encrucijada \r\nCentro comercial el portal, local Nº 01,\r\nsector valle lindo, frente al cementerio municipal Turmero', 'Telefono: (0244) 663.45.50\r\nCorreo:tribunalagrarioturmero@gmail.com', 1, '2026-04-20 17:36:13'),
(5, 'Juzgado de primera instancia en lo civil, mercantil, transito y bancario, de la circunscripcion judicial del estado aragua', 'Cagua calle froilan Correa, centro comercial\r\nDoriana, piso 3.', 'DRA. Magaly Bastia Telefono: (0414) 589.16.19\r\nSecretaria: Ismerli Puerta Telefono: (0424) 262.45.27', 1, '2026-04-20 17:38:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tribunales`
--

CREATE TABLE `tribunales` (
  `tribunal` varchar(163) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `Id_tribunal` int(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tribunales`
--

INSERT INTO `tribunales` (`tribunal`, `Id_tribunal`) VALUES
('1RA INSTANCIA CIVIL CAGUA', 69),
('CIRCUITO JUDICIAL CON COMPETENCIA EN DELITOS DE VIOLENCIA CONTRA LA MUJER DE LA CIRCUNSCRIPCIÓN JUDICIAL DEL ESTADO ARAGUA', 60),
('CORTE DE APELACIONES Nº I', 38),
('CORTE DE APELACIONES Nº II', 39),
('CORTE DE APELACIONES Nº III', 40),
('JUZGADO  CUARTO DE PRIMERA INSTANCIA DE JUICIO', 27),
('JUZGADO  PRIMERO DE PRIMERA INSTANCIA DE JUICIO', 25),
('JUZGADO  SUPERIOR  1º DEL  TRABAJO', 32),
('JUZGADO  TERCERO DE PRIMERA INSTANCIA DE JUICIO', 26),
('JUZGADO 10° DE SUSTANCIACIÓN MEDIACIÓN Y EJECUCIÓN', 35),
('JUZGADO 11° DE SUSTANCIACIÓN MEDIACIÓN Y EJECUCIÓN', 36),
('JUZGADO 12° DE SUSTANCIACIÓN MEDIACIÓN Y EJECUCIÓN', 37),
('JUZGADO 1º DE SUSTANCIACIÓN  MEDIACIÓN Y  EJECUCIÓN DEL TRABAJO', 28),
('JUZGADO 2º DE SUSTANCIACIÓN  MEDIACIÓN Y  EJECUCIÓN DEL TRABAJO', 29),
('JUZGADO 3º DE SUSTANCIACIÓN  MEDIACIÓN Y  EJECUCIÓN DEL TRABAJO', 30),
('JUZGADO 4º DE SUSTANCIACIÓN  MEDIACIÓN Y  EJECUCIÓN DEL TRABAJO', 31),
('JUZGADO 5º DE SUSTANCIACIÓN  MEDIACIÓN Y  EJECUCIÓN DEL TRABAJO', 34),
('JUZGADO CUARTO DE PRIMERA INSTANCIA EN LO CIVIL, MERCANTIL  Y DEL TRANSITO DE LA CIRCUNSCRIPCIÓN JUDICIAL DEL ESTADO ARAGUA', 8),
('Juzgado Cuarto de Primera Intancia en lo civil, mercantil y de transito de la circuncripcion judicial del estado aragua', 71),
('JUZGADO DEL MUNICIPIO ZAMORA', 64),
('JUZGADO MUNICIPIO MARIÑO TURMERO DE LA CIRCUNCRIPCION JUDICIAL DEL ESTADO ARAGUA', 65),
('JUZGADO PRIMERO DE PRIMERA INSTANCIA CIVIL Y MERCANTIL DE LA CIRCUNSCRIPCIÓN JUDICIAL DEL ESTADO ARAGUA', 5),
('JUZGADO SEGUNDO DE PRIMERA INSTANCIA CIVIL Y MERCANTIL DE LA CIRCUNSCRIPCIÓN JUDICIAL DEL ESTADO ARAGUA', 6),
('JUZGADO SEGUNDO DE PRIMERA INSTANCIA EN LO CIVIL Y MERCANTIL DE LA CIRCUNCRIPCION JUDICIAL DEL ESTADO ARAGUA', 65),
('JUZGADO SUPERIOR 3° DEL TRABAJO', 33),
('JUZGADO SUPERIOR AGRARIO DE LA CIRCUNSCRIPCIÓN JUDICIAL DE LOS ESTADOS ARAGUA Y CARABOBO', 4),
('JUZGADO SUPERIOR DE PROTECCIÓN DE NIÑOS, NIÑAS Y ADOLESCENTES', 14),
('JUZGADO SUPERIOR ESTADAL CONTENCIOSO ADMINISTRATIVO DE LA CIRCUNSCRIPCIÓN JUDICIAL DEL ESTADO ARAGUA', 62),
('JUZGADO SUPERIOR PRIMERO EN LO CIVIL, MERCANTIL, BANCARIO Y DEL TRANSITO DE LA CIRCUNSCRIPCIÓN JUDICIAL DEL ESTADO ARAGUA', 1),
('JUZGADO SUPERIOR SEGUNDO EN LO CIVIL, MERCANTIL, BANCARIO Y DEL TRANSITO DE LA CIRCUNSCRIPCIÓN JUDICIAL DEL ESTADO ARAGUA', 3),
('JUZGADO TERCERO DE PRIMERA INSTANCIA EN LO CIVIL Y MERCANTIL DE LA CIRCUNSCRIPCIÓN JUDICIAL DEL ESTADO ARAGUA', 7),
('RECTORÍA JUDICIAL DE LA CIRCUNSCRIPCIÓN JUDICIAL DEL ESTADO ARAGUA', 2),
('SALA 1 DE JUCIO DEL JUZGADO UNIPERSONAL DE PROTECCION DEL NIÑO, NIÑA Y ADOLECENTE', 70),
('TRIBUNAL  DE PRIMERA INSTANCIA CIVIL   LA VICTORIA', 64),
('TRIBUNAL  PRIMERO DE MUNICIPIO ORDINARIO Y EJECUTOR DE MEDIDAS DE LOS MUNICIPIOS GIRARDOT Y MARIO BRICEÑO IRAGORRY DE LA CIRCUNSCRIPCIÓN JUDICIAL DEL ESTADO ARAGUA', 9),
('TRIBUNAL  QUINTO DE MUNICIPIO ORDINARIO Y EJECUTOR DE MEDIDAS DE LOS MUNICIPIOS GIRARDOT Y MARIO BRICEÑO IRAGORRY DE LA CIRCUNSCRIPCIÓN JUDICIAL DEL ESTADO ARAGUA', 13),
('TRIBUNAL  SEGUNDO DE MUNICIPIO ORDINARIO Y EJECUTOR DE MEDIDAS DE LOS MUNICIPIOS GIRARDOT Y MARIO BRICEÑO IRAGORRY DE LA CIRCUNSCRIPCIÓN JUDICIAL DEL ESTADO ARAGUA', 10),
('TRIBUNAL  TERCERO DE MUNICIPIO ORDINARIO Y EJECUTOR DE MEDIDAS DE LOS MUNICIPIOS GIRARDOT Y MARIO BRICEÑO IRAGORRY DE LA CIRCUNSCRIPCIÓN JUDICIAL DEL ESTADO ARAGUA', 11),
('TRIBUNAL 1RO DE MENORES (EXTINTO)', 66),
('TRIBUNAL 1° DE EJECUCION', 57),
('TRIBUNAL 1° DE JUICIO', 51),
('Tribunal 2do de Menores (Extinto)', 63),
('TRIBUNAL 2° DE EJECUCION', 58),
('TRIBUNAL 2° DE JUICIO', 52),
('TRIBUNAL 3° DE EJECUCION', 59),
('TRIBUNAL 3° DE JUICIO', 53),
('TRIBUNAL 4° DE JUICIO', 54),
('TRIBUNAL 5° DE JUICIO', 55),
('TRIBUNAL 6° DE JUICIO', 56),
('TRIBUNAL CUARTO DE MUNICIPIO ORDINARIO Y EJECUTOR DE MEDIDAS DE LOS MUNICIPIOS GIRARDOT Y MARIO BRICEÑO IRAGORRY DE LA CIRCUNSCRIPCIÓN JUDICIAL DEL ESTADO ARAGUA', 12),
('TRIBUNAL CUARTO DE PRIMERA INSTANCIA DE MEDIACIÓN Y SUSTANCIACIÓN DE PROTECCIÓN DE NIÑOS, NIÑAS Y ADOLESCENTES', 18),
('TRIBUNAL DE PRIMERA INSTANCIA CIVIL LA VICTORIA', 67),
('TRIBUNAL DE PROTECCION  DEL NIÑO , NIÑA, Y DEL ADOLECENTE SALA 03', 68),
('TRIBUNAL OCTAVO DE PRIMERA INSTANCIA DE MEDIACIÓN Y SUSTANCIACIÓN DE PROTECCIÓN DE NIÑOS, NIÑAS Y ADOLESCENTES', 22),
('TRIBUNAL PRIMERO DE MUNICIPIO ORDINARIO Y EJECUTOR DE MEDIDAS DE LOS MUNICIPIOS SUCRE Y JOSE ANGEL LAMAS DE LA CIRCUNCRIPCION JUDICIAL DEL ESTADO ARAGUA', 73),
('TRIBUNAL PRIMERO DE PRIMERA INSTANCIA DE JUICIO DE PROTECCIÓN DE NIÑOS, NIÑAS Y ADOLESCENTES', 23),
('TRIBUNAL PRIMERO DE PRIMERA INSTANCIA DE MEDIACIÓN Y SUSTANCIACIÓN DE PROTECCIÓN DE NIÑOS, NIÑAS Y ADOLESCENTES', 15),
('TRIBUNAL PRIMERO DE PRIMERA INSTANCIA MUNICIPAL  EN FUNCIONES DE CONTROL DEL MUNICIPIO GIRARDOT', 61),
('TRIBUNAL QUINTO DE PRIMERA INSTANCIA DE MEDIACIÓN Y SUSTANCIACIÓN DE PROTECCIÓN DE NIÑOS, NIÑAS Y ADOLESCENTES', 19),
('TRIBUNAL SEGUNDO DE MUNICIPIO ORDINARIO Y EJECUTOR DE MEDIDAS DEL MUNICIPIO SANTIAGO MARIÑO DE LA CIRCUNCRIPCION JUDICIAL DEL ESTADO ARAGUA', 72),
('TRIBUNAL SEGUNDO DE PRIMERA INSTANCIA DE JUICIO DE PROTECCIÓN DE NIÑOS, NIÑAS Y ADOLESCENTES', 24),
('TRIBUNAL SEGUNDO DE PRIMERA INSTANCIA DE MEDIACIÓN Y SUSTANCIACIÓN DE PROTECCIÓN DE NIÑOS, NIÑAS Y ADOLESCENTES', 16),
('TRIBUNAL SÉPTIMO DE PRIMERA INSTANCIA DE MEDIACIÓN Y SUSTANCIACIÓN DE PROTECCIÓN DE NIÑOS, NIÑAS Y ADOLESCENTES', 21),
('TRIBUNAL SEXTO DE PRIMERA INSTANCIA DE MEDIACIÓN Y SUSTANCIACIÓN DE PROTECCIÓN DE NIÑOS, NIÑAS Y ADOLESCENTES', 20),
('TRIBUNAL TERCERO DE PRIMERA INSTANCIA DE MEDIACIÓN Y SUSTANCIACIÓN DE PROTECCIÓN DE NIÑOS, NIÑA Y ADOLESCENTES', 17),
('TRIBUNALES 10° DE CONTROL', 50),
('TRIBUNALES 1° DE CONTROL', 41),
('TRIBUNALES 2° DE CONTROL', 42),
('TRIBUNALES 3° DE CONTROL', 43),
('TRIBUNALES 4° DE CONTROL', 44),
('TRIBUNALES 5° DE CONTROL', 45),
('TRIBUNALES 6° DE CONTROL', 46),
('TRIBUNALES 7° DE CONTROL', 47),
('TRIBUNALES 8° DE CONTROL', 48),
('TRIBUNALES 9° DE CONTROL', 49);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_sistema`
--

CREATE TABLE `usuarios_sistema` (
  `id_usuario` int(11) NOT NULL,
  `nombre_full` varchar(100) NOT NULL,
  `usuario_nick` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rol` enum('admin','operador') DEFAULT 'operador',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios_sistema`
--

INSERT INTO `usuarios_sistema` (`id_usuario`, `nombre_full`, `usuario_nick`, `password_hash`, `rol`, `fecha_registro`, `status`) VALUES
(1, 'Administrador', 'admin', '$2y$10$ekEEUxhWgZnX0JOiKVwDSOo4f1Wq8LTNGiTjR2DwgME3C8Uetr7Fi', 'admin', '2026-04-10 14:16:15', 1),
(2, 'eduardo perez', 'eduardo', '$2y$10$yWrqDONmsosnIsVbI13JO.ITtfvJcQ3E4REEtvHta8UifVuats5VS', 'operador', '2026-04-10 14:55:13', 1),
(3, 'christian aja', 'chris', '$2y$10$WJ12WN4dHQLvrz6vxQbC4OjsJsYZD0.W/72R.Uv6PCWlWUJafMejO', 'operador', '2026-04-10 17:53:08', 0),
(4, 'Pasante', 'Pasante1', '$2y$10$spqsxMtn32cJD2/1p/3/OOcJhBAQHpE14mfS1GOkt3LQc374P5WZG', 'operador', '2026-04-17 15:34:54', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `auditoria_log`
--
ALTER TABLE `auditoria_log`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `historial_movimientos`
--
ALTER TABLE `historial_movimientos`
  ADD PRIMARY KEY (`id_movimiento`),
  ADD KEY `fk_expediente_maestro` (`n_expediente`),
  ADD KEY `fk_usuario_mov` (`id_usuario`);

--
-- Indices de la tabla `maestro`
--
ALTER TABLE `maestro`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `n_expediente` (`n_expediente`),
  ADD KEY `idx_sede` (`id_sede`);

--
-- Indices de la tabla `sedes_deposito`
--
ALTER TABLE `sedes_deposito`
  ADD PRIMARY KEY (`id_sede`),
  ADD UNIQUE KEY `nombre_sede` (`nombre_sede`),
  ADD UNIQUE KEY `nombre_sede_2` (`nombre_sede`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indices de la tabla `usuarios_sistema`
--
ALTER TABLE `usuarios_sistema`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `usuario_nick` (`usuario_nick`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `auditoria_log`
--
ALTER TABLE `auditoria_log`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=278;

--
-- AUTO_INCREMENT de la tabla `historial_movimientos`
--
ALTER TABLE `historial_movimientos`
  MODIFY `id_movimiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT de la tabla `maestro`
--
ALTER TABLE `maestro`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `sedes_deposito`
--
ALTER TABLE `sedes_deposito`
  MODIFY `id_sede` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `usuarios_sistema`
--
ALTER TABLE `usuarios_sistema`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `auditoria_log`
--
ALTER TABLE `auditoria_log`
  ADD CONSTRAINT `auditoria_log_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios_sistema` (`id_usuario`) ON DELETE SET NULL;

--
-- Filtros para la tabla `historial_movimientos`
--
ALTER TABLE `historial_movimientos`
  ADD CONSTRAINT `fk_expediente_maestro` FOREIGN KEY (`n_expediente`) REFERENCES `maestro` (`n_expediente`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usuario_historial` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios_sistema` (`id_usuario`),
  ADD CONSTRAINT `fk_usuario_mov` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios_sistema` (`id_usuario`);

--
-- Filtros para la tabla `maestro`
--
ALTER TABLE `maestro`
  ADD CONSTRAINT `fk_maestro_sede` FOREIGN KEY (`id_sede`) REFERENCES `sedes_deposito` (`id_sede`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
