-- =========================================================
-- Reservas de alojamiento: estructura + datos de ejemplo
-- Compatible con MySQL 8.0.16+ y MariaDB 10.4+
-- Importar sobre una base de datos ya creada:
--   mysql -u USUARIO -p NOMBRE_BD < database/schema.sql
-- =========================================================

SET NAMES utf8mb4;

-- Primero la tabla hija (la que tiene la clave foránea)
DROP TABLE IF EXISTS reserva_evento;
DROP TABLE IF EXISTS reserva;

CREATE TABLE reserva (
    id                 INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    nombre_huesped     VARCHAR(150)  NOT NULL,
    email_huesped      VARCHAR(254)  NOT NULL,
    nombre_alojamiento VARCHAR(150)  NOT NULL,
    fecha_entrada      DATE          NOT NULL,
    fecha_salida       DATE          NOT NULL,
    estado             ENUM('pendiente', 'confirmada', 'cancelada') NOT NULL DEFAULT 'pendiente',
    importe            DECIMAL(10,2) NOT NULL,
    notas              TEXT          NULL,
    fecha_creacion     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_reserva_estado_entrada (estado, fecha_entrada),
    INDEX idx_reserva_entrada (fecha_entrada),
    INDEX idx_reserva_huesped (nombre_huesped),

    CONSTRAINT chk_reserva_fechas  CHECK (fecha_salida > fecha_entrada),
    CONSTRAINT chk_reserva_importe CHECK (importe >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reserva_evento (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    reserva_id     INT UNSIGNED NOT NULL,
    tipo           VARCHAR(30)  NOT NULL,
    descripcion    VARCHAR(255) NOT NULL,
    fecha_creacion DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_evento_reserva_fecha (reserva_id, fecha_creacion),

    CONSTRAINT fk_evento_reserva
        FOREIGN KEY (reserva_id) REFERENCES reserva (id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- Datos de ejemplo: estados variados, fechas pasadas y futuras,
-- nombres con tildes y apellidos repetidos para probar filtros
-- =========================================================

INSERT INTO reserva (id, nombre_huesped, email_huesped, nombre_alojamiento, fecha_entrada, fecha_salida, estado, importe, notas, fecha_creacion) VALUES
(1,  'Lucía Fernández Ruiz',  'lucia.fernandez@example.com',  'Hotel Mirador del Mar (Málaga)',          '2026-10-20', '2026-10-24', 'confirmada', 480.00,  'Llegada tarde, sobre las 22:00', '2026-09-15 10:12:00'),
(2,  'Javier Martín López',   'javier.martin@example.com',    'Casa Rural El Olivar (Cáceres)',          '2026-11-06', '2026-11-08', 'pendiente',  220.00,  NULL,                             '2026-10-10 18:40:00'),
(3,  'María José García',     'mariajose.garcia@example.com', 'Apartamentos Plaza Mayor (Salamanca)',    '2026-10-02', '2026-10-05', 'confirmada', 315.50,  'Necesita cuna para bebé',        '2026-08-28 09:05:00'),
(4,  'Carlos Sánchez Pérez',  'carlos.sanchez@example.com',   'Hotel Sierra Nevada Suites (Granada)',    '2026-12-27', '2027-01-02', 'pendiente',  1290.00, 'Habitación con vistas',          '2026-10-12 21:15:00'),
(5,  'Ana Belén Torres',      'anabelen.torres@example.com',  'Hostal Puerta del Sol (Madrid)',          '2026-10-16', '2026-10-18', 'cancelada',  150.00,  NULL,                             '2026-09-30 12:00:00'),
(6,  'Diego Ramírez Ortega',  'diego.ramirez@example.com',    'Hotel Mirador del Mar (Málaga)',          '2026-11-14', '2026-11-21', 'confirmada', 910.00,  NULL,                             '2026-10-01 16:30:00'),
(7,  'Sofía Navarro Gil',     'sofia.navarro@example.com',    'Apartamentos Playa Dorada (Benidorm)',    '2026-10-25', '2026-10-31', 'pendiente',  540.00,  'Solicita plaza de parking',      '2026-10-13 08:50:00'),
(8,  'Pablo Moreno Castillo', 'pablo.moreno@example.com',     'Casa Rural El Olivar (Cáceres)',          '2026-09-12', '2026-09-14', 'cancelada',  210.00,  NULL,                             '2026-08-20 19:22:00'),
(9,  'Elena Jiménez Romero',  'elena.jimenez@example.com',    'Hotel Ría de Vigo (Vigo)',                '2026-12-05', '2026-12-08', 'confirmada', 375.00,  'Celebran su aniversario',        '2026-10-05 11:11:00'),
(10, 'Íñigo Etxeberria',      'inigo.etxeberria@example.com', 'Hotel Bahía de la Concha (San Sebastián)', '2026-11-01', '2026-11-03', 'pendiente',  398.00,  NULL,                             '2026-10-14 09:30:00'),
(11, 'Laura Domínguez Vidal', 'laura.dominguez@example.com',  'Hostal Puerta del Sol (Madrid)',          '2026-10-09', '2026-10-11', 'confirmada', 160.00,  NULL,                             '2026-09-25 14:45:00'),
(12, 'Marta García Sanz',     'marta.garcia@example.com',     'Apartamentos Plaza Mayor (Salamanca)',    '2027-02-13', '2027-02-15', 'cancelada',  230.00,  'Cambio de planes',               '2026-10-08 20:05:00');


INSERT INTO reserva_evento (reserva_id, tipo, descripcion, fecha_creacion) VALUES
(1,  'creada',     'Reserva creada',     '2026-09-15 10:12:00'),
(1,  'confirmada', 'Reserva confirmada', '2026-09-16 09:00:00'),
(2,  'creada',     'Reserva creada',     '2026-10-10 18:40:00'),
(3,  'creada',     'Reserva creada',     '2026-08-28 09:05:00'),
(3,  'confirmada', 'Reserva confirmada', '2026-08-28 12:30:00'),
(4,  'creada',     'Reserva creada',     '2026-10-12 21:15:00'),
(5,  'creada',     'Reserva creada',     '2026-09-30 12:00:00'),
(5,  'cancelada',  'Reserva cancelada',  '2026-10-03 17:45:00'),
(6,  'creada',     'Reserva creada',     '2026-10-01 16:30:00'),
(6,  'confirmada', 'Reserva confirmada', '2026-10-02 10:05:00'),
(7,  'creada',     'Reserva creada',     '2026-10-13 08:50:00'),
(8,  'creada',     'Reserva creada',     '2026-08-20 19:22:00'),
(8,  'confirmada', 'Reserva confirmada', '2026-08-21 09:40:00'),
(8,  'cancelada',  'Reserva cancelada',  '2026-09-05 13:10:00'),
(9,  'creada',     'Reserva creada',     '2026-10-05 11:11:00'),
(9,  'confirmada', 'Reserva confirmada', '2026-10-06 08:20:00'),
(10, 'creada',     'Reserva creada',     '2026-10-14 09:30:00'),
(11, 'creada',     'Reserva creada',     '2026-09-25 14:45:00'),
(11, 'confirmada', 'Reserva confirmada', '2026-09-26 10:00:00'),
(12, 'creada',     'Reserva creada',     '2026-10-08 20:05:00'),
(12, 'cancelada',  'Reserva cancelada',  '2026-10-11 18:30:00');