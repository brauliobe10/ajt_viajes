-- ============================================================
-- Funcion del archivo: Define la estructura de tablas y relaciones de la base de datos.
--  VIAJES AJT — Schema (estructura solamente)
--  Base de datos: viajes_ajt_3fn
--  Ejecutar primero este archivo antes de seed.sql
-- ============================================================

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;

CREATE DATABASE IF NOT EXISTS viajes_ajt_3fn
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE viajes_ajt_3fn;

-- -----------------------------------------------------------
-- 1. TABLAS MAESTRAS (sin dependencias)
-- -----------------------------------------------------------

CREATE TABLE IF NOT EXISTS roles (
    id_rol       TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre_rol   VARCHAR(30) NOT NULL UNIQUE,
    activo       TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS paises (
    id_pais    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre     VARCHAR(80) NOT NULL,
    continente VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categorias_paquete (
    id_categoria SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre       VARCHAR(80)  NOT NULL,
    slug         VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS metodos_pago (
    id_metodo TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre    VARCHAR(60) NOT NULL UNIQUE,
    activo    TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- 2. TABLAS DE PRIMER NIVEL (1 dependencia)
-- -----------------------------------------------------------

CREATE TABLE IF NOT EXISTS ciudades (
    id_ciudad  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_pais    INT UNSIGNED NOT NULL,
    nombre     VARCHAR(80) NOT NULL,
    codigo_iata CHAR(3) NULL,
    FOREIGN KEY (id_pais) REFERENCES paises(id_pais) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS servicios_visa (
    id_servicio    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_pais        INT UNSIGNED NOT NULL,
    tipo_visa      VARCHAR(80)   NOT NULL,
    precio_asesoria DECIMAL(10,2) NOT NULL,
    disponible     TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (id_pais) REFERENCES paises(id_pais) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_rol           TINYINT UNSIGNED NOT NULL DEFAULT 1,
    id_pais_nacionalidad INT UNSIGNED NULL,
    nombres          VARCHAR(80)  NOT NULL,
    apellidos        VARCHAR(80)  NOT NULL,
    email            VARCHAR(120) NOT NULL UNIQUE,
    password_hash    VARCHAR(255) NOT NULL,
    dni_pasaporte    VARCHAR(20) NULL UNIQUE,
    telefono         VARCHAR(20) NULL,
    created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol) ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (id_pais_nacionalidad) REFERENCES paises(id_pais) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- 3. CATÁLOGO PRINCIPAL
-- -----------------------------------------------------------

CREATE TABLE IF NOT EXISTS paquetes (
    id_paquete       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_categoria     SMALLINT UNSIGNED NOT NULL,
    id_ciudad        INT UNSIGNED NOT NULL,
    nombre           VARCHAR(160) NOT NULL,
    slug             VARCHAR(180) NOT NULL UNIQUE,
    descripcion_corta VARCHAR(255) NULL,
    descripcion      TEXT NULL,
    itinerario       TEXT NULL COMMENT 'Legacy JSON — datos ahora en paquete_itinerario',
    precio_base      DECIMAL(10,2) NOT NULL,
    precio_anterior  DECIMAL(10,2) NULL,
    duracion_dias    TINYINT UNSIGNED NOT NULL,
    duracion_noches  TINYINT UNSIGNED NOT NULL,
    cupo_maximo      SMALLINT UNSIGNED NULL,
    dificultad       VARCHAR(40) NULL,
    hotel            VARCHAR(160) NULL,
    habitacion       VARCHAR(120) NULL,
    comidas          VARCHAR(160) NULL,
    vuelos           VARCHAR(160) NULL,
    movilidad        VARCHAR(160) NULL,
    guia             VARCHAR(160) NULL,
    destacado        TINYINT(1) NOT NULL DEFAULT 0,
    disponible       TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (id_categoria) REFERENCES categorias_paquete(id_categoria) ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (id_ciudad) REFERENCES ciudades(id_ciudad) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS paquete_imagenes (
    id_imagen   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_paquete  INT UNSIGNED NOT NULL,
    url_imagen  VARCHAR(300) NOT NULL,
    es_principal TINYINT(1) NOT NULL DEFAULT 0,
    orden       SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    FOREIGN KEY (id_paquete) REFERENCES paquetes(id_paquete) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS paquete_incluye (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_paquete INT UNSIGNED NOT NULL,
    texto      VARCHAR(255) NOT NULL,
    orden      SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    FOREIGN KEY (id_paquete) REFERENCES paquetes(id_paquete) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS paquete_no_incluye (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_paquete INT UNSIGNED NOT NULL,
    texto      VARCHAR(255) NOT NULL,
    orden      SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    FOREIGN KEY (id_paquete) REFERENCES paquetes(id_paquete) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS paquete_politicas (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_paquete  INT UNSIGNED NOT NULL,
    titulo      VARCHAR(120) NOT NULL,
    descripcion TEXT NOT NULL,
    orden       SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    FOREIGN KEY (id_paquete) REFERENCES paquetes(id_paquete) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS paquete_documentos (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_paquete INT UNSIGNED NOT NULL,
    texto      VARCHAR(255) NOT NULL,
    orden      SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    FOREIGN KEY (id_paquete) REFERENCES paquetes(id_paquete) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS paquete_tags (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_paquete INT UNSIGNED NOT NULL,
    texto      VARCHAR(80) NOT NULL,
    orden      SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    FOREIGN KEY (id_paquete) REFERENCES paquetes(id_paquete) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS paquete_itinerario (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_paquete  INT UNSIGNED NOT NULL,
    dia         SMALLINT UNSIGNED NOT NULL,
    titulo      VARCHAR(120) NOT NULL,
    descripcion TEXT NOT NULL,
    orden       SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    FOREIGN KEY (id_paquete) REFERENCES paquetes(id_paquete) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- 4. MOTOR TRANSACCIONAL
-- -----------------------------------------------------------

CREATE TABLE IF NOT EXISTS solicitudes_visa (
    id_solicitud     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario       INT UNSIGNED NOT NULL,
    id_servicio      INT UNSIGNED NOT NULL,
    codigo_solicitud VARCHAR(20) NOT NULL UNIQUE,
    fecha_viaje_aprox DATE NULL,
    estado           ENUM('nueva','contactado','documentos_pendientes','en_revision','en_tramite','aprobada','rechazada','cancelada') NOT NULL DEFAULT 'nueva',
    created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario)  REFERENCES usuarios(id_usuario)     ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (id_servicio) REFERENCES servicios_visa(id_servicio) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS reservas (
    id_reserva       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario       INT UNSIGNED NOT NULL,
    codigo_reserva   VARCHAR(20) NOT NULL UNIQUE,
    telefono_pasajero VARCHAR(20) NULL,
    descuento        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    estado           ENUM('pendiente','pagado','cancelado') NOT NULL DEFAULT 'pendiente',
    created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS detalle_reservas (
    id_detalle       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_reserva       INT UNSIGNED NOT NULL,
    id_paquete       INT UNSIGNED NOT NULL,
    fecha_viaje      DATE NOT NULL,
    cantidad_pasajeros TINYINT UNSIGNED NOT NULL,
    precio_unitario  DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_reserva) REFERENCES reservas(id_reserva) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (id_paquete) REFERENCES paquetes(id_paquete) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS viajeros_reserva (
    id_viajero   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_detalle   INT UNSIGNED NOT NULL,
    nombres      VARCHAR(80) NOT NULL,
    apellidos    VARCHAR(80) NOT NULL,
    num_documento VARCHAR(20) NOT NULL,
    FOREIGN KEY (id_detalle) REFERENCES detalle_reservas(id_detalle) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pagos (
    id_pago          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario       INT UNSIGNED NOT NULL,
    id_metodo_pago   TINYINT UNSIGNED NOT NULL,
    id_reserva       INT UNSIGNED NULL,
    id_solicitud_visa INT UNSIGNED NULL,
    numero_operacion VARCHAR(80) NOT NULL,
    monto            DECIMAL(10,2) NOT NULL,
    comprobante_url  VARCHAR(500) NULL,
    estado           ENUM('pendiente','validado','rechazado') NOT NULL DEFAULT 'pendiente',
    created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario)       REFERENCES usuarios(id_usuario)       ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (id_metodo_pago)   REFERENCES metodos_pago(id_metodo)    ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (id_reserva)       REFERENCES reservas(id_reserva)       ON UPDATE CASCADE ON DELETE SET NULL,
    FOREIGN KEY (id_solicitud_visa) REFERENCES solicitudes_visa(id_solicitud) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- 5. TABLAS AUXILIARES
-- -----------------------------------------------------------

CREATE TABLE IF NOT EXISTS mensajes_contacto (
    id_mensaje INT AUTO_INCREMENT PRIMARY KEY,
    nombre     VARCHAR(150) NOT NULL,
    email      VARCHAR(200) NOT NULL,
    telefono   VARCHAR(30) DEFAULT NULL,
    asunto     VARCHAR(200) NOT NULL,
    mensaje    TEXT NOT NULL,
    leido      TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS testimonios (
    id_testimonio INT AUTO_INCREMENT PRIMARY KEY,
    nombre        VARCHAR(150) NOT NULL,
    ciudad        VARCHAR(100) NOT NULL,
    comentario    TEXT NOT NULL,
    rating        DECIMAL(2,1) NOT NULL DEFAULT 5.0,
    servicio      VARCHAR(100) NULL,
    activo        TINYINT(1) NOT NULL DEFAULT 1,
    es_demo       TINYINT(1) NOT NULL DEFAULT 0,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS configuracion (
    clave       VARCHAR(100) NOT NULL,
    valor       TEXT NOT NULL,
    descripcion VARCHAR(255) DEFAULT NULL,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (clave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reclamaciones (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id         INT UNSIGNED NOT NULL,
    reserva_id         INT UNSIGNED NULL,
    solicitud_visa_id  INT UNSIGNED NULL,
    tipo               ENUM('reclamo','sugerencia') NOT NULL,
    categoria          VARCHAR(100) NOT NULL,
    descripcion        TEXT NOT NULL,
    pedido_cliente     TEXT NULL,
    medio_respuesta    ENUM('whatsapp','correo','telefono') NOT NULL DEFAULT 'correo',
    estado             ENUM('nuevo','en_revision','respondido','cerrado') NOT NULL DEFAULT 'nuevo',
    respuesta_admin    TEXT NULL,
    created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id)        REFERENCES usuarios(id_usuario)            ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (reserva_id)        REFERENCES reservas(id_reserva)            ON UPDATE CASCADE ON DELETE SET NULL,
    FOREIGN KEY (solicitud_visa_id) REFERENCES solicitudes_visa(id_solicitud)  ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- 6. SEGURIDAD Y CONTROL
-- -----------------------------------------------------------

CREATE TABLE IF NOT EXISTS rate_limits (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_identifier (identifier),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
