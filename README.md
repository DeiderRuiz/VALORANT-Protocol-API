## VALORANT Protocol - API

VALORANT Protocol es una API para una plataforma de gestión de proyectos y equipos de trabajo inspirada en el universo de Valorant. Permite administrar agentes, crear y asignar misiones, supervisar su progreso y consultar estadísticas generales e individuales.

## Funcionalidades principales
- **Autenticación y seguridad:** autenticación mediante JWT, autorización basada en roles y recuperación de contraseñas.
- **Gestión de agentes:** registro, edición y administración de agentes, incluyendo sus habilidades y perfiles.
- **Gestión de misiones:** creación, asignación de agentes, edición y seguimiento de misiones.
- **Ciclo de vida de las misiones:** control de estados, validación de resultados y gestión de solicitudes de aborto.
- **Sistema de notificaciones:** notificaciones persistentes para los agentes sobre eventos relevantes de sus misiones.
- **Procesamiento en segundo plano:** jobs y tareas programadas para supervisar misiones inconclusas y notificar sobre sus plazos.
- **Estadísticas:** consulta de estadísticas generales y específicas de los agentes.

## Arquitectura y características técnicas

- **Arquitectura MVC:** organización del backend mediante modelos, controladores y separación de responsabilidades.
- **Capa de servicios:** centralización de la lógica de negocio en clases de servicio, manteniendo los controladores ligeros.
- **Autenticación JWT:** autenticación sin estado mediante tokens JSON Web Token.
- **Middleware:** control de autenticación y autorización, con restricciones de acceso según los roles de usuario.
- **Policies:** autorización de acciones específicas sobre las misiones según los permisos y la relación del usuario con ellas.
- **Procesamiento asíncrono:** ejecución de trabajos en segundo plano mediante Jobs y colas para el envío de notificaciones.
- **Tareas programadas:** Commands y Scheduler de Laravel para supervisar misiones inconclusas y gestionar notificaciones relacionadas con sus plazos.

## Tecnologías usadas
- Laravel 12
- JWT Authentication
- MySQL
