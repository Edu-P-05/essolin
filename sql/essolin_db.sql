-- 1. Crear la base de datos
CREATE DATABASE IF NOT EXISTS essolin_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE essolin_db;

-- 2. Tabla maestra de Roles
CREATE TABLE roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL
);

-- 3. Tabla de Usuarios (Conectada a roles)
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre_completo VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    id_rol INT NOT NULL,
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol)
);

-- 4. Tabla maestra de Cuadrillas
CREATE TABLE cuadrillas (
    id_cuadrilla INT AUTO_INCREMENT PRIMARY KEY,
    nombre_cuadrilla VARCHAR(100) NOT NULL
);

-- 5. Tabla maestra de Tipos de Trabajo
CREATE TABLE tipos_trabajo (
    id_tipo INT AUTO_INCREMENT PRIMARY KEY,
    nombre_tipo VARCHAR(100) NOT NULL
);

-- 6. Tabla principal de Trabajos (Conectada a cuadrillas, tipos y usuarios)
CREATE TABLE trabajos (
    id_trabajo INT AUTO_INCREMENT PRIMARY KEY,
    id_cuadrilla INT NOT NULL,
    id_tipo INT NOT NULL,
    ubicacion VARCHAR(255) NOT NULL,
    descripcion TEXT NOT NULL,
    id_usuario INT NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cuadrilla) REFERENCES cuadrillas(id_cuadrilla),
    FOREIGN KEY (id_tipo) REFERENCES tipos_trabajo(id_tipo),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

-- 7. Tabla de Evidencias (Conectada a los trabajos)
CREATE TABLE evidencias (
    id_evidencia INT AUTO_INCREMENT PRIMARY KEY,
    id_trabajo INT NOT NULL,
    ruta_archivo VARCHAR(255) NOT NULL,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_trabajo) REFERENCES trabajos(id_trabajo) ON DELETE CASCADE
);

-- INSERTS BÁSICOS PARA QUE PUEDAS EMPEZAR A PROBAR EL SISTEMA

-- Crear los roles
INSERT INTO roles (nombre_rol) VALUES ('Administrador'), ('Supervisor'), ('Secretaria');

-- Crear un usuario de prueba (Se le asigna id_rol = 1, es decir, Administrador)
INSERT INTO usuarios (nombre_completo, email, password, id_rol) 
VALUES ('Juan Perez', 'juan@essolin.com', '$2y$10$7R3vXo7DclZpC7bZ2g8OEu9jFzZ5UoK9vX9M0F3KzZ4g8h9i7jK6e', 1);

-- Crear algunas cuadrillas
INSERT INTO cuadrillas (nombre_cuadrilla) VALUES ('Cuadrilla Norte 01'), ('Cuadrilla Sur 02'), ('Cuadrilla Centro 01');

-- Crear algunos tipos de trabajo
INSERT INTO tipos_trabajo (nombre_tipo) VALUES ('Mantenimiento Preventivo'), ('Reparación de Avería'), ('Inspección Técnica');