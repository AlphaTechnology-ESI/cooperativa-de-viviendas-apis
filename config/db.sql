CREATE DATABASE IF NOT EXISTS cooperativa_cooptrack;
USE cooperativa_cooptrack;

CREATE TABLE IF NOT EXISTS usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    id_persona INT UNIQUE,
    nom_usu VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL UNIQUE,
    telefono VARCHAR(20),
    cedula VARCHAR(8) NOT NULL UNIQUE,
    fecha_nacimiento DATE,
    estado_civil ENUM('soltero', 'casado', 'divorciado', 'viudo', 'union_convivencial'),
    ocupacion VARCHAR(100),
    ingresos ENUM('hasta_500000', '500000_1000000', '1000000_1500000', '1500000_2000000', 'mas_2000000'),
    contrasena VARCHAR(50)
);

CREATE TABLE IF NOT EXISTS usuario_pendiente (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nom_usu VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL UNIQUE,
    telefono VARCHAR(20),
    cedula VARCHAR(8) NOT NULL UNIQUE,
    fecha_nacimiento DATE,
    estado_civil ENUM('soltero', 'casado', 'divorciado', 'viudo', 'union_convivencial'),
    ocupacion VARCHAR(100),
    ingresos ENUM('hasta_500000', '500000_1000000', '1000000_1500000', '1500000_2000000', 'mas_2000000'),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('pendiente', 'aprobado', 'rechazado', 'en_revision') DEFAULT 'pendiente'
);

CREATE TABLE IF NOT EXISTS admins (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    id_persona INT UNIQUE,
    nom_admin VARCHAR(50),
    correo VARCHAR(100),
    telefono VARCHAR(20),
    tipo_admin ENUM('tesorero', 'presidente', 'secretario'),
    contrasena VARCHAR(50),
    FOREIGN KEY (id_persona) REFERENCES usuario(id_persona)
);

CREATE TABLE IF NOT EXISTS unidad_habitacional (
    id_unidad INT AUTO_INCREMENT PRIMARY KEY,
    numero INT,
    estado VARCHAR(50),
    etapa_construccion VARCHAR(50),
    calle VARCHAR(100),
    numero_direccion VARCHAR(10),
    fecha_asignacion DATE
);

CREATE TABLE IF NOT EXISTS asignaunidad (
    id_asignacion INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    id_admin INT,
    id_unidad INT,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_admin) REFERENCES admins(id_admin),
    FOREIGN KEY (id_unidad) REFERENCES unidad_habitacional(id_unidad)
);

CREATE TABLE IF NOT EXISTS pago_mensual (
    id_pago INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    estado_pago VARCHAR(50),
    comprobante_pago VARCHAR(100),
    fecha DATE,
    fecha_envio DATE,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
);

CREATE TABLE IF NOT EXISTS aporte_inicial (
    id_aporte INT AUTO_INCREMENT PRIMARY KEY,
    estado_validacion VARCHAR(50),
    comprobante_pago VARCHAR(100),
    fecha DATE,
    id_usuario INT,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
);

CREATE TABLE IF NOT EXISTS jornada_trabajo (
    id_jornada INT AUTO_INCREMENT PRIMARY KEY,
    tipo_compensacion VARCHAR(50) DEFAULT NULL,
    motivo_inasistencia VARCHAR(100) DEFAULT NULL,
    horas_trabajadas INT NOT NULL,
    fecha DATE NOT NULL,
    id_usuario INT NOT NULL,
    comprobante LONGBLOB DEFAULT NULL,
    comprobante_nombre VARCHAR(255) DEFAULT NULL,
    estado ENUM('pendiente','aprobada','rechazada') DEFAULT 'pendiente',
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
);

CREATE TABLE IF NOT EXISTS solicitud_unidad_habitacional (
    ID_Solicitud INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    Vivienda_Seleccionada VARCHAR(100) NOT NULL,
    Monto_Inicial DECIMAL(12,2),
    Forma_Pago ENUM('contado', 'financiado', 'mixto'),
    Grupo_Familiar TEXT,
    Comentarios TEXT,
    Estado_Solicitud ENUM('pendiente', 'en_revision', 'aprobada', 'rechazada') DEFAULT 'pendiente',
    Fecha_Solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Fecha_Evaluacion TIMESTAMP NULL,
    Comentarios_Admin TEXT,
    FOREIGN KEY (id_usuario) REFERENCES usuario_pendiente(id_usuario) ON DELETE CASCADE
);



INSERT INTO admins (nom_admin, correo, contrasena)
SELECT * FROM (SELECT 'Admin', 'admin@gmail.com', '4321') AS tmp
WHERE NOT EXISTS (
  SELECT 1 FROM admins WHERE correo = 'admin@gmail.com'
) LIMIT 1;

INSERT INTO usuario (nom_usu, correo, cedula, contrasena)
SELECT * FROM (SELECT 'Usuario', 'user@gmail.com', '56690127', '1234') AS tmp
WHERE NOT EXISTS (
  SELECT 1 FROM usuario WHERE correo = 'user@gmail.com'
) LIMIT 1;



INSERT INTO usuario_pendiente 
(nom_usu, correo, telefono, cedula, fecha_nacimiento, estado_civil, ocupacion, ingresos)
SELECT * FROM (SELECT 'Juan Pérez', 'juan.perez@gmail.com', '099123456', '12345678', '1990-05-12', 'soltero', 'Ingeniero', '1000000_1500000') AS tmp
WHERE NOT EXISTS (
    SELECT 1 FROM usuario_pendiente WHERE correo = 'juan.perez@gmail.com'
) LIMIT 1;

INSERT INTO usuario_pendiente 
(nom_usu, correo, telefono, cedula, fecha_nacimiento, estado_civil, ocupacion, ingresos)
SELECT * FROM (SELECT 'María Gómez', 'maria.gomez@gmail.com', '098765432', '87654321', '1985-11-30', 'casado', 'Abogada', '1500000_2000000') AS tmp
WHERE NOT EXISTS (
    SELECT 1 FROM usuario_pendiente WHERE correo = 'maria.gomez@gmail.com'
) LIMIT 1;

INSERT INTO usuario_pendiente 
(nom_usu, correo, telefono, cedula, fecha_nacimiento, estado_civil, ocupacion, ingresos)
SELECT * FROM (SELECT 'Carlos López', 'carlos.lopez@gmail.com', '091234567', '11223344', '1995-07-20', 'soltero', 'Docente', '500000_1000000') AS tmp
WHERE NOT EXISTS (
    SELECT 1 FROM usuario_pendiente WHERE correo = 'carlos.lopez@gmail.com'
) LIMIT 1;



INSERT INTO solicitud_unidad_habitacional 
(id_usuario, Vivienda_Seleccionada, Monto_Inicial, Forma_Pago, Estado_Solicitud)
SELECT * FROM (SELECT 1, 'Unidad 101', 500000, 'contado', 'pendiente') AS tmp
WHERE NOT EXISTS (
  SELECT 1 FROM solicitud_unidad_habitacional WHERE id_usuario = 1
) LIMIT 1;

INSERT INTO solicitud_unidad_habitacional 
(id_usuario, Vivienda_Seleccionada, Monto_Inicial, Forma_Pago, Estado_Solicitud)
SELECT * FROM (SELECT 2, 'Unidad 102', 750000, 'financiado', 'pendiente') AS tmp
WHERE NOT EXISTS (
  SELECT 1 FROM solicitud_unidad_habitacional WHERE id_usuario = 2
) LIMIT 1;

INSERT INTO solicitud_unidad_habitacional
(id_usuario, Vivienda_Seleccionada, Monto_Inicial, Forma_Pago, Estado_Solicitud)
SELECT * FROM (SELECT 3, 'Unidad 103', 600000, 'mixto', 'pendiente') AS tmp
WHERE NOT EXISTS (
  SELECT 1 FROM solicitud_unidad_habitacional WHERE id_usuario = 3
) LIMIT 1;

