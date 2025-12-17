# Task Manager Beta Avanzada

## ¿Qué es?

Task Manager es un sistema completo de gestión de proyectos y tareas desarrollado con **Laravel y Vue 3**, enfocado en la **colaboración en tiempo real** y la productividad de equipos.

Está inspirado en herramientas como **Jira** y **ClickUp**, pero construido como una aplicación moderna, modular y escalable.

---

## ¿Qué hace?

- **Gestión de Proyectos**: Crea y administra proyectos con equipos, estados personalizados y plantillas reutilizables
- **Kanban Board**: Visualiza y gestiona tareas con drag & drop en un tablero Kanban interactivo
- **Colaboración en Tiempo Real**: Trabaja simultáneamente con otros usuarios, ve indicadores de escritura, seguimiento de cursores y resolución de conflictos
- **Sistema de Comentarios**: Comenta en tareas y proyectos con soporte para archivos adjuntos y menciones
- **Gestión de Archivos**: Adjunta archivos a tareas, proyectos y comentarios con preview de imágenes y PDFs, compresión automática y versionado
- **Notificaciones**: Recibe notificaciones en tiempo real y por email sobre actividades importantes
- **Dashboard y KPIs**: Visualiza métricas, gráficos y estadísticas de tus proyectos y tareas
- **Roles y Permisos**: Sistema granular de permisos con roles globales y específicos por proyecto
- **Dependencias de Tareas**: Define relaciones entre tareas con bloqueo automático de tareas dependientes

## Demo

🚧 Próximamente  
(Screenshots y video del tablero Kanban y colaboración en tiempo real)

---

## ¿Cómo lo instalo?

### Requisitos
- PHP >= 8.2
- Composer >= 2.0
- Node.js >= 18
- MySQL >= 8 (o MariaDB >= 10.4)
- Redis >= 6.0 (recomendado)

### Instalación

1. **Clonar el repositorio**
```bash
git clone https://github.com/leranuva/task_manager.git
cd task_manager_beta
```

2. **Instalar dependencias PHP**
```bash
composer install
```

3. **Instalar dependencias Node**
```bash
npm install
```

4. **Configurar entorno**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Configurar base de datos**
```bash
# Crear base de datos
mysql -u root -p < database/create_database.sql

# Ejecutar migraciones
php artisan migrate

# Ejecutar seeders
php artisan db:seed
```

6. **Crear enlace simbólico de storage**
```bash
php artisan storage:link
```

7. **Compilar assets**
```bash
npm run dev
# o para producción
npm run build
```

8. **Iniciar servidor**
```bash
php artisan serve
```

9. **Iniciar queue worker** (en otra terminal)
```bash
php artisan queue:work
```

## ¿Por qué es especial?

- **Tecnología Moderna**: Construido con Laravel 12, Vue 3, Inertia.js y Tailwind CSS para una experiencia de usuario fluida
- **Tiempo Real**: Integración completa con Pusher para actualizaciones instantáneas, indicadores de escritura y seguimiento de cursores
- **Colaboración Avanzada**: Resolución automática de conflictos, historial de actividad y presencia de usuarios en tiempo real
- **Escalable**: Arquitectura modular con servicios reutilizables, políticas de autorización granulares y soporte para almacenamiento en la nube (S3)
- **Completo**: Sistema integral que incluye gestión de proyectos, tareas, comentarios, archivos, notificaciones y análisis
- **Seguro**: Sistema robusto de roles y permisos con control de acceso a nivel de proyecto y tarea
- **Productivo**: Dashboard con KPIs, gráficos interactivos y métricas para tomar decisiones informadas
