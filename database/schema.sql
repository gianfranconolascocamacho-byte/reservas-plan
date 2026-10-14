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