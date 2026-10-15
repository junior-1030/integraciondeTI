# NEXUS TI / CHUGUN - Sistema Integral de Gestión y Control

Proyecto desarrollado para la asignatura de **Integración de Tecnologías de Información**. Combina una arquitectura web moderna con un portal de presentación empresarial y un panel administrativo completo (CRUD) conectado a MySQL mediante PHP PDO.

---

## Módulos del Proyecto

1. **Panel de Gestión y Control (CRUD)** (`/CRUD/index.php`):
   - **Métricas y KPIs**: Resumen de usuarios totales, activos, administradores y estado del servidor.
   - **Gestión Completa de Usuarios (CRUD)**:
     - **Crear**: Modal interactivo con validación de datos en tiempo real.
     - **Leer**: Directorio interactivo con avatares dinámicos, badges de rol y estado.
     - **Actualizar**: Edición ágil tanto en modal como en vista individual (`editar.php`).
     - **Eliminar**: Borrado seguro mediante peticiones `POST` y confirmación modal.
   - **Búsqueda y Filtros en Vivo**: Filtrado instantáneo por texto (nombre, correo, teléfono), rol y estado sin recargar la página.
   - **Exportación**: Descarga directa de datos a formato CSV.
   - **Modo Oscuro / Claro**: Selector de tema persistente mediante `localStorage`.

2. **Sitio Web Público (Landing Page)** (`/Proyecto GitHub/index.html`):
   - Landing page corporativa construida con Bootstrap 5.
   - Enlace directo e integración con el panel administrativo.

---

## 🛠️ Requisitos e Instalación en XAMPP

### 1. Prerrequisitos
- **XAMPP** (con Apache y MySQL activados).
- **PHP 8.0 o superior** con extensión `pdo_mysql` habilitada.

### 2. Configurar la Base de Datos
1. Abre tu navegador e ingresa a [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
2. Crea una nueva base de datos llamada `crud_db` con cotejamiento `utf8mb4_unicode_ci` (o simplemente importa el script).
3. Haz clic en la pestaña **Importar** y selecciona el archivo [`database.sql`](database.sql) ubicado en la raíz de este proyecto.
4. Presiona **Continuar** para ejecutar la creación de tablas y datos de muestra.

### 3. Ejecutar el Proyecto
- **Acceso al Panel Administrativo (CRUD):**
  [http://localhost/CRUD/CRUD/index.php](http://localhost/CRUD/CRUD/index.php)

- **Acceso a la Landing Page:**
  [http://localhost/CRUD/Proyecto%20GitHub/index.html](http://localhost/CRUD/Proyecto%20GitHub/index.html)

---

## 🔒 Seguridad Implementada
- **Sentencias preparadas (Prepared Statements PDO)** en todas las consultas para prevenir inyección SQL.
- **Transacciones de eliminación vía POST** para mitigar vulnerabilidades CSRF y borrados accidentales.
- **Sanitización y validación estricta** en servidor (`filter_var`, `htmlspecialchars`, verificación de correos duplicados).
- **Manejo de mensajes Flash** en sesión (`$_SESSION['flash']`).
