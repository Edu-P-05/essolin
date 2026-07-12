-- CREAR LA BASE DE DATOS Y SELECCIONARLA
IF NOT EXISTS (SELECT * FROM sys.databases WHERE name = 'essolin_db')
BEGIN
    CREATE DATABASE essolin_db;
END
GO

USE essolin_db;
GO

-- 1. CREACIÓN DE TABLAS (SIN LLAVES FORÁNEAS TODAVÍA)

CREATE TABLE roles (
  id_rol INT IDENTITY(1,1) NOT NULL,
  nombre_rol NVARCHAR(50) NOT NULL,
  CONSTRAINT PK_roles PRIMARY KEY (id_rol)
);

CREATE TABLE cuadrillas (
  id_cuadrilla INT IDENTITY(1,1) NOT NULL,
  nombre_cuadrilla NVARCHAR(100) NOT NULL,
  CONSTRAINT PK_cuadrillas PRIMARY KEY (id_cuadrilla)
);

CREATE TABLE tipos_trabajo (
  id_tipo INT IDENTITY(1,1) NOT NULL,
  nombre_tipo NVARCHAR(100) NOT NULL,
  CONSTRAINT PK_tipos PRIMARY KEY (id_tipo)
);

CREATE TABLE usuarios (
  id_usuario INT IDENTITY(1,1) NOT NULL,
  nombre_completo NVARCHAR(100) NOT NULL,
  email NVARCHAR(100) NOT NULL,
  password NVARCHAR(255) NOT NULL,
  id_rol INT NOT NULL,
  estado NVARCHAR(20) DEFAULT 'Activo',
  CONSTRAINT PK_usuarios PRIMARY KEY (id_usuario),
  CONSTRAINT UQ_email UNIQUE (email)
);

CREATE TABLE trabajos (
  id_trabajo INT IDENTITY(1,1) NOT NULL,
  id_cuadrilla INT NOT NULL,
  id_tipo INT NOT NULL,
  ubicacion NVARCHAR(255) NOT NULL,
  descripcion NVARCHAR(MAX) NOT NULL,
  id_usuario INT NOT NULL,
  fecha_registro DATETIME NOT NULL DEFAULT GETDATE(),
  estado NVARCHAR(20) NOT NULL DEFAULT 'Programado',
  fecha_finalizacion DATETIME NULL,
  fecha_programada DATE NULL,
  CONSTRAINT PK_trabajos PRIMARY KEY (id_trabajo)
);

CREATE TABLE bitacora_trabajos (
  id_comentario INT IDENTITY(1,1) NOT NULL,
  id_trabajo INT NOT NULL,
  id_usuario INT NOT NULL,
  comentario NVARCHAR(MAX) NOT NULL,
  fecha_comentario DATETIME NOT NULL DEFAULT GETDATE(),
  CONSTRAINT PK_bitacora PRIMARY KEY (id_comentario)
);

CREATE TABLE evidencias (
  id_evidencia INT IDENTITY(1,1) NOT NULL,
  id_trabajo INT NOT NULL,
  ruta_archivo VARBINARY(MAX) NULL,
  fecha_subida DATETIME NOT NULL DEFAULT GETDATE(),
  CONSTRAINT PK_evidencias PRIMARY KEY (id_evidencia)
);

-- 2. CREACIÓN DE RESTRICCIONES Y LLAVES FORÁNEAS (RELACIONES)

ALTER TABLE usuarios 
  ADD CONSTRAINT FK_usuarios_rol FOREIGN KEY (id_rol) REFERENCES roles (id_rol);

ALTER TABLE trabajos 
  ADD CONSTRAINT FK_trabajos_cuadrilla FOREIGN KEY (id_cuadrilla) REFERENCES cuadrillas (id_cuadrilla),
  ADD CONSTRAINT FK_trabajos_tipo FOREIGN KEY (id_tipo) REFERENCES tipos_trabajo (id_tipo),
  ADD CONSTRAINT FK_trabajos_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario);

ALTER TABLE bitacora_trabajos 
  ADD CONSTRAINT FK_bitacora_trabajo FOREIGN KEY (id_trabajo) REFERENCES trabajos (id_trabajo),
  ADD CONSTRAINT FK_bitacora_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario);

ALTER TABLE evidencias 
  ADD CONSTRAINT FK_evidencias_trabajo FOREIGN KEY (id_trabajo) REFERENCES trabajos (id_trabajo) ON DELETE CASCADE;

-- 3. INSERCIÓN DE DATOS (VOLCADO)

SET IDENTITY_INSERT roles ON;
INSERT INTO roles (id_rol, nombre_rol) VALUES
(1, 'Administrador'), (2, 'Supervisor'), (3, 'Técnico'), (4, 'Secretaria');
SET IDENTITY_INSERT roles OFF;

SET IDENTITY_INSERT cuadrillas ON;
INSERT INTO cuadrillas (id_cuadrilla, nombre_cuadrilla) VALUES
(1, 'Cuadrilla Alpha (Norte)'), (2, 'Cuadrilla Beta (Sur)'), (3, 'Cuadrilla Gamma (Centro)');
SET IDENTITY_INSERT cuadrillas OFF;

SET IDENTITY_INSERT tipos_trabajo ON;
INSERT INTO tipos_trabajo (id_tipo, nombre_tipo) VALUES
(1, 'Mantenimiento Preventivo'), (2, 'Reparación de Avería'), (3, 'Instalación de Tableros'), (4, 'Diagnóstico de Fallas');
SET IDENTITY_INSERT tipos_trabajo OFF;

SET IDENTITY_INSERT usuarios ON;
INSERT INTO usuarios (id_usuario, nombre_completo, email, password, id_rol, estado) VALUES
(1, 'Juan Pérez (Admin)', 'admin@essolin.com', '$2y$10$Hd8L/Ko7.sUTWbP3Jkn8cuqFLJsELk7vMTuHjt198SjL5vFvYWEHy', 1, 'Suspendido'),
(2, 'Ana Gómez (Supervisor)', 'ana@essolin.com', '$2y$10$Hd8L/Ko7.sUTWbP3Jkn8cuqFLJsELk7vMTuHjt198SjL5vFvYWEHy', 2, 'Suspendido'),
(3, 'Carlos Torres (Técnico)', 'carlos@essolin.com', '$2y$10$Hd8L/Ko7.sUTWbP3Jkn8cuqFLJsELk7vMTuHjt198SjL5vFvYWEHy', 3, 'Suspendido'),
(4, 'Elena Vargas (Secretaria)', 'elena@essolin.com', '$2y$10$Hd8L/Ko7.sUTWbP3Jkn8cuqFLJsELk7vMTuHjt198SjL5vFvYWEHy', 4, 'Suspendido'),
(5, 'Edú Perleche', '123@gmail.com', '$2y$10$l1HipmKh5Ku2JLmbS8Z0dufV11UYBGbISH9ArfgXC5gnxtrYm1tO6', 1, 'Activo'),
(6, 'Tecnicoxd', 'tecnico@gmail.com', '$2y$10$/d8CZGEkqtQeGtX.XJkUWuZAr54mmtP51MNWhPiAF3FoW1t07wRxa', 3, 'Activo'),
(7, 'Supervisor123', 'Supervisor@gmail.com', '$2y$10$gAIUVnNvyTnayOoD/Mo/5OBh4bEXMDNlHKsoRuC7YR3Tm9kzt9HsG', 2, 'Activo'),
(8, 'Secretaria123', 'Secretaria@gmail.com', '$2y$10$cuLAGS6xQAAqn4sbOhOd.OlxkZ2A4rzFLTJfeCCV96G12T.ldrYBa', 4, 'Activo'),
(9, 'asd', 'sadad@gmail.com', '$2y$10$iv26dSJv03t9RgQsPKmVyuAMSxiaUrzAzSTzmolI0qcQsa2bA9HyK', 1, 'Suspendido'),
(10, 'Roxana Alex Sanchez Campos', 'Roxana@gmail.com', '$2y$10$w9PmW8j9K3H1a84ASnDNYOf4ZPTzZQKeohIYO8AEMC9E1NOOsaBK.', 4, 'Activo');
SET IDENTITY_INSERT usuarios OFF;

SET IDENTITY_INSERT trabajos ON;
INSERT INTO trabajos (id_trabajo, id_cuadrilla, id_tipo, ubicacion, descripcion, id_usuario, fecha_registro, estado, fecha_finalizacion, fecha_programada) VALUES
(1, 1, 1, 'Planta Norte - Sector A', 'Se realizó limpieza y ajuste de bornes en el tablero principal. Todo operativo y dentro de los parámetros.', 3, '2026-05-10 13:30:00', 'Finalizado', NULL, NULL),
(2, 2, 2, 'Subestación Sur', 'Se reemplazó un disyuntor termomagnético dañado por sobrecarga. Pruebas de tensión exitosas.', 3, '2026-05-12 19:15:00', 'Finalizado', '2026-06-13 19:22:24', NULL),
(3, 3, 3, 'Taller Central', 'Instalación de nuevo tablero de control para motores de la zona de empaque. Cableado estructurado.', 2, '2026-05-15 14:00:00', 'Programado', NULL, NULL),
(4, 1, 4, 'Almacén 02', 'Se reportó parpadeo de luces. Se detectó falla en el cable neutro. Se requiere programación para cambio de cableado completo.', 2, '2026-05-18 21:45:00', 'Finalizado', '2026-06-13 17:58:04', NULL),
(5, 1, 1, 'Planta Norte - A', 'Trabajito pue', 1, '2026-05-19 19:27:44', 'En Proceso', NULL, NULL),
(8, 2, 2, 'Taller Oeste', 'Se realizó limpieza', 1, '2026-06-13 22:58:58', 'Programado', NULL, NULL);
SET IDENTITY_INSERT trabajos OFF;

SET IDENTITY_INSERT bitacora_trabajos ON;
INSERT INTO bitacora_trabajos (id_comentario, id_trabajo, id_usuario, comentario, fecha_comentario) VALUES
(1, 8, 1, 'se avanzó', '2026-06-13 23:22:56'),
(2, 8, 1, 'okey', '2026-06-13 23:22:59'),
(3, 8, 1, 'yara', '2026-06-13 23:23:03');
SET IDENTITY_INSERT bitacora_trabajos OFF;