CREATE DATABASE IF NOT EXISTS cooperativa_cooptrack;
USE cooperativa_cooptrack;

CREATE TABLE IF NOT EXISTS usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    id_persona INT AUTO_INCREMENT UNIQUE,
    nomusu VARCHAR(50),
    correo VARCHAR(100),
    contrasena VARCHAR(50)
);

CREATE TABLE IF NOT EXISTS admins (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    id_persona INT AUTO_INCREMENT,
    nomadm VARCHAR(50),
    correo VARCHAR(100),
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
    tipo_compensacion VARCHAR(50),
    motivo_inasistencia VARCHAR(100),
    horas_trabajadas INT,
    fecha DATE,
    id_usuario INT,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
);

CREATE TABLE IF NOT EXISTS usuario_pendiente (
    id_usuario INT PRIMARY KEY,
    nom_usu VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL UNIQUE,
    telefono VARCHAR(20),
    DNI VARCHAR(20) NOT NULL UNIQUE,
    Fecha_Nacimiento DATE,
    Estado_Civil ENUM('soltero', 'casado', 'divorciado', 'viudo', 'union_convivencial'),
    Ocupacion VARCHAR(100),
    Ingresos ENUM('hasta_500000', '500000_1000000', '1000000_1500000', '1500000_2000000', 'mas_2000000'),
    Direccion TEXT,
    Fecha_Registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Estado ENUM('pendiente', 'aprobado', 'activo', 'suspendido', 'rechazado') DEFAULT 'pendiente'
);

CREATE TABLE IF NOT EXISTS solicitud_unidad_habitacional (
    ID_Solicitud INT PRIMARY KEY,
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

INSERT INTO admins (correo, contrasena)
SELECT * FROM (SELECT 'admin@gmail.com', '4321') AS tmp
WHERE NOT EXISTS (
  SELECT 1 FROM admins WHERE correo = 'admin@gmail.com'
) LIMIT 1;


INSERT INTO usuario (correo, contrasena)
SELECT * FROM (SELECT 'user@gmail.com', '1234') AS tmp
WHERE NOT EXISTS (
  SELECT 1 FROM usuario WHERE correo = 'user@gmail.com'
) LIMIT 1;
