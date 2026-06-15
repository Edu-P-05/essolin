
CREATE TABLE `bitacora_trabajos` (
  `id_comentario` int(11) NOT NULL,
  `id_trabajo` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `comentario` text NOT NULL,
  `fecha_comentario` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `bitacora_trabajos`
--

INSERT INTO `bitacora_trabajos` (`id_comentario`, `id_trabajo`, `id_usuario`, `comentario`, `fecha_comentario`) VALUES
(1, 8, 1, 'se avanzó', '2026-06-13 23:22:56'),
(2, 8, 1, 'okey', '2026-06-13 23:22:59'),
(3, 8, 1, 'yara', '2026-06-13 23:23:03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuadrillas`
--

CREATE TABLE `cuadrillas` (
  `id_cuadrilla` int(11) NOT NULL,
  `nombre_cuadrilla` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cuadrillas`
--

INSERT INTO `cuadrillas` (`id_cuadrilla`, `nombre_cuadrilla`) VALUES
(1, 'Cuadrilla Alpha (Norte)'),
(2, 'Cuadrilla Beta (Sur)'),
(3, 'Cuadrilla Gamma (Centro)');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evidencias`
--

CREATE TABLE `evidencias` (
  `id_evidencia` int(11) NOT NULL,
  `id_trabajo` int(11) NOT NULL,
  `ruta_archivo` longblob DEFAULT NULL,
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `evidencias`
--
CREATE TABLE 'roles' (
  `id_rol` int(11) NOT NULL,
  `nombre_rol` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre_rol`) VALUES
(1, 'Administrador'),
(2, 'Supervisor'),
(3, 'Técnico'),
(4, 'Secretaria');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_trabajo`
--

CREATE TABLE `tipos_trabajo` (
  `id_tipo` int(11) NOT NULL,
  `nombre_tipo` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tipos_trabajo`
--

INSERT INTO `tipos_trabajo` (`id_tipo`, `nombre_tipo`) VALUES
(1, 'Mantenimiento Preventivo'),
(2, 'Reparación de Avería'),
(3, 'Instalación de Tableros'),
(4, 'Diagnóstico de Fallas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trabajos`
--

CREATE TABLE `trabajos` (
  `id_trabajo` int(11) NOT NULL,
  `id_cuadrilla` int(11) NOT NULL,
  `id_tipo` int(11) NOT NULL,
  `ubicacion` varchar(255) NOT NULL,
  `descripcion` text NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` varchar(20) NOT NULL DEFAULT 'Programado',
  `fecha_finalizacion` datetime DEFAULT NULL,
  `fecha_programada` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `trabajos`
--

INSERT INTO `trabajos` (`id_trabajo`, `id_cuadrilla`, `id_tipo`, `ubicacion`, `descripcion`, `id_usuario`, `fecha_registro`, `estado`, `fecha_finalizacion`, `fecha_programada`) VALUES
(1, 1, 1, 'Planta Norte - Sector A', 'Se realizó limpieza y ajuste de bornes en el tablero principal. Todo operativo y dentro de los parámetros.', 3, '2026-05-10 13:30:00', 'Finalizado', NULL, NULL),
(2, 2, 2, 'Subestación Sur', 'Se reemplazó un disyuntor termomagnético dañado por sobrecarga. Pruebas de tensión exitosas.', 3, '2026-05-12 19:15:00', 'Finalizado', '2026-06-13 19:22:24', NULL),
(3, 3, 3, 'Taller Central', 'Instalación de nuevo tablero de control para motores de la zona de empaque. Cableado estructurado.', 2, '2026-05-15 14:00:00', 'Programado', NULL, NULL),
(4, 1, 4, 'Almacén 02', 'Se reportó parpadeo de luces. Se detectó falla en el cable neutro. Se requiere programación para cambio de cableado completo.', 2, '2026-05-18 21:45:00', 'Finalizado', '2026-06-13 17:58:04', NULL),
(5, 1, 1, 'Planta Norte - A', 'Trabajito pue', 1, '2026-05-19 19:27:44', 'En Proceso', NULL, NULL),
(6, 1, 1, 'fdsad', 'asdada', 1, '2026-05-19 22:58:57', 'Finalizado', NULL, NULL),
(7, 1, 1, 'asdsad', 'asdada', 1, '2026-05-19 23:02:25', 'Finalizado', '2026-06-13 17:57:10', NULL),
(8, 2, 2, 'asdsad', 'sdasd', 1, '2026-06-13 22:58:58', 'Programado', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `estado` varchar(20) DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre_completo`, `email`, `password`, `id_rol`, `estado`) VALUES
(1, 'Juan Pérez (Admin)', 'admin@essolin.com', '$2y$10$Hd8L/Ko7.sUTWbP3Jkn8cuqFLJsELk7vMTuHjt198SjL5vFvYWEHy', 1, 'Suspendido'),
(2, 'Ana Gómez (Supervisor)', 'ana@essolin.com', '$2y$10$Hd8L/Ko7.sUTWbP3Jkn8cuqFLJsELk7vMTuHjt198SjL5vFvYWEHy', 2, 'Suspendido'),
(3, 'Carlos Torres (Técnico)', 'carlos@essolin.com', '$2y$10$Hd8L/Ko7.sUTWbP3Jkn8cuqFLJsELk7vMTuHjt198SjL5vFvYWEHy', 3, 'Suspendido'),
(4, 'Elena Vargas (Secretaria)', 'elena@essolin.com', '$2y$10$Hd8L/Ko7.sUTWbP3Jkn8cuqFLJsELk7vMTuHjt198SjL5vFvYWEHy', 4, 'Suspendido'),
(5, 'Edú Perleche', '123@gmail.com', '$2y$10$l1HipmKh5Ku2JLmbS8Z0dufV11UYBGbISH9ArfgXC5gnxtrYm1tO6', 1, 'Activo'),
(6, 'Tecnicoxd', 'tecnico@gmail.com', '$2y$10$/d8CZGEkqtQeGtX.XJkUWuZAr54mmtP51MNWhPiAF3FoW1t07wRxa', 3, 'Activo'),
(7, 'Supervisor123', 'Supervisor@gmail.com', '$2y$10$gAIUVnNvyTnayOoD/Mo/5OBh4bEXMDNlHKsoRuC7YR3Tm9kzt9HsG', 2, 'Activo'),
(8, 'Secretaria123', 'Secretaria@gmail.com', '$2y$10$cuLAGS6xQAAqn4sbOhOd.OlxkZ2A4rzFLTJfeCCV96G12T.ldrYBa', 4, 'Activo'),
(9, 'asd', 'sadad@gmail.com', '$2y$10$iv26dSJv03t9RgQsPKmVyuAMSxiaUrzAzSTzmolI0qcQsa2bA9HyK', 1, 'Suspendido');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `bitacora_trabajos`
--
ALTER TABLE `bitacora_trabajos`
  ADD PRIMARY KEY (`id_comentario`),
  ADD KEY `id_trabajo` (`id_trabajo`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `cuadrillas`
--
ALTER TABLE `cuadrillas`
  ADD PRIMARY KEY (`id_cuadrilla`);

--
-- Indices de la tabla `evidencias`
--
ALTER TABLE `evidencias`
  ADD PRIMARY KEY (`id_evidencia`),
  ADD KEY `id_trabajo` (`id_trabajo`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `tipos_trabajo`
--
ALTER TABLE `tipos_trabajo`
  ADD PRIMARY KEY (`id_tipo`);

--
-- Indices de la tabla `trabajos`
--
ALTER TABLE `trabajos`
  ADD PRIMARY KEY (`id_trabajo`),
  ADD KEY `id_cuadrilla` (`id_cuadrilla`),
  ADD KEY `id_tipo` (`id_tipo`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `id_rol` (`id_rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `bitacora_trabajos`
--
ALTER TABLE `bitacora_trabajos`
  MODIFY `id_comentario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `cuadrillas`
--
ALTER TABLE `cuadrillas`
  MODIFY `id_cuadrilla` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `evidencias`
--
ALTER TABLE `evidencias`
  MODIFY `id_evidencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tipos_trabajo`
--
ALTER TABLE `tipos_trabajo`
  MODIFY `id_tipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `trabajos`
--
ALTER TABLE `trabajos`
  MODIFY `id_trabajo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `bitacora_trabajos`
--
ALTER TABLE `bitacora_trabajos`
  ADD CONSTRAINT `bitacora_trabajos_ibfk_1` FOREIGN KEY (`id_trabajo`) REFERENCES `trabajos` (`id_trabajo`),
  ADD CONSTRAINT `bitacora_trabajos_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `evidencias`
--
ALTER TABLE `evidencias`
  ADD CONSTRAINT `evidencias_ibfk_1` FOREIGN KEY (`id_trabajo`) REFERENCES `trabajos` (`id_trabajo`) ON DELETE CASCADE;

--
-- Filtros para la tabla `trabajos`
--
ALTER TABLE `trabajos`
  ADD CONSTRAINT `trabajos_ibfk_1` FOREIGN KEY (`id_cuadrilla`) REFERENCES `cuadrillas` (`id_cuadrilla`),
  ADD CONSTRAINT `trabajos_ibfk_2` FOREIGN KEY (`id_tipo`) REFERENCES `tipos_trabajo` (`id_tipo`),
  ADD CONSTRAINT `trabajos_ibfk_3` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
