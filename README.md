# 🚀 Task Manager Avanzado

Sistema completo de gestión de proyectos y tareas inspirado en **Jira** y **ClickUp**, desarrollado con Laravel 12 y Vue 3. Plataforma colaborativa en tiempo real con gestión avanzada de equipos, proyectos, permisos y análisis.

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![Vue](https://img.shields.io/badge/Vue-3.x-4FC08D.svg)](https://vuejs.org)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

---

## ✨ Características Principales

- 🎯 **Gestión de Proyectos** - Equipos, proyectos, tareas con estados personalizados
- 👥 **Sistema de Equipos** - Colaboración con roles granulares (Owner, Admin, Member, Viewer)
- 📊 **Panel de Super Admin** - Dashboard con métricas, gráficos y estadísticas
- 🔐 **Permisos Granulares** - Sistema de autorización basado en Policies (Laravel)
- 📝 **Kanban Board** - Tablero interactivo con drag & drop
- 💬 **Colaboración en Tiempo Real** - WebSockets con Laravel Reverb
- 📎 **Gestión de Archivos** - Adjuntos con preview, compresión y versionado
- 🔔 **Notificaciones** - Tiempo real y por email
- 📈 **Dashboard y KPIs** - Métricas y gráficos interactivos
- 🔗 **Dependencias de Tareas** - Relaciones con bloqueo automático

---

## 🛠️ Stack Tecnológico

### Backend
- **Laravel 12** - Framework PHP
- **MySQL 8+** - Base de datos
- **Redis 6+** - Cache y Queue
- **Laravel Reverb** - WebSockets
- **Laravel Breeze** - Autenticación

### Frontend
- **Vue 3** - Framework JavaScript
- **Inertia.js** - SPA sin API
- **Tailwind CSS** - Framework CSS
- **Chart.js** - Gráficos y visualizaciones
- **Vue Draggable** - Drag & drop

---

## 📋 Requisitos

- PHP >= 8.2
- Composer >= 2.0
- Node.js >= 18
- MySQL >= 8.0 (o MariaDB >= 10.4)
- Redis >= 6.0 (recomendado)

---

## 🚀 Instalación Rápida

```bash
# 1. Clonar repositorio
git clone https://github.com/leranuva/task_manager.git
cd task_manager_beta

# 2. Instalar dependencias
composer install
npm install

# 3. Configurar entorno
cp .env.example .env
php artisan key:generate

# 4. Configurar base de datos en .env
DB_DATABASE=task_manager
DB_USERNAME=root
DB_PASSWORD=

# 5. Ejecutar migraciones
php artisan migrate

# ⚠️ Nota: El primer Super Admin debe crearse manualmente
# Opción 1: Actualizar directamente en la base de datos
# UPDATE users SET is_super_admin = 1 WHERE id = 1;
# Opción 2: Usar un seeder personalizado
# php artisan tinker
# >>> User::find(1)->update(['is_super_admin' => true]);

# 6. Crear enlace de storage
php artisan storage:link

# 7. Compilar assets
npm run dev

# 8. Iniciar servidor
php artisan serve

# 9. Iniciar queue worker (terminal separada)
php artisan queue:work

# 10. Iniciar Reverb (terminal separada, opcional)
php artisan reverb:start
```

**Acceso**: `http://localhost:8000`

---

## 📁 Estructura del Proyecto

```
task_manager_beta/
├── app/
│   ├── Http/Controllers/     # Controladores (Dashboard, Projects, Tasks, etc.)
│   ├── Models/                # Modelos Eloquent (User, Team, Project, Task, etc.)
│   ├── Policies/              # Políticas de autorización
│   ├── Services/              # Servicios (Permission, Notification, etc.)
│   └── Enums/                 # Enumeraciones (TeamRole, ProjectRole)
├── database/
│   ├── migrations/            # Migraciones de base de datos
│   └── seeders/               # Seeders para datos iniciales
├── resources/
│   ├── js/
│   │   ├── Pages/             # Componentes Vue (Inertia)
│   │   └── Components/         # Componentes reutilizables
│   └── views/                 # Vistas Blade
├── routes/
│   └── web.php                # Rutas de la aplicación
├── tests/
│   └── Feature/               # Tests de funcionalidades
└── docs/                      # Documentación completa
```

---

## 🎯 Características Detalladas

### Sistema de Usuarios y Permisos

- **Super Admin**: Acceso total al sistema (gestión de usuarios, panel admin)
- **Equipos**: Owner, Admin, Member, Viewer
- **Proyectos**: Owner, Admin, Editor, Viewer
- **Herencia de Permisos**: Equipo → Proyecto
- **Policies Laravel**: Autorización granular

> **Nota**: Los roles no se almacenan en tablas independientes. Se manejan como enums (`TeamRole`, `ProjectRole`) y campos string en tablas pivote (`team_user.role`, `project_user.role`), con autorización centralizada en Laravel Policies. Esto evita la complejidad de sistemas RBAC tradicionales como Spatie.

### Gestión de Equipos

- Crear, editar, eliminar equipos
- Invitar miembros por email
- Asignar roles a miembros
- Transferir ownership
- Archivar equipos

### Gestión de Proyectos

- CRUD completo de proyectos
- Estados personalizados (Kanban)
- Plantillas reutilizables
- Miembros y permisos
- Actividad y comentarios
- Archivos adjuntos

### Panel de Super Admin

- Dashboard con métricas generales
- Gráficos de crecimiento (usuarios, equipos, proyectos)
- Usuarios recientes
- Equipos y proyectos más activos
- Acceso: `/admin/dashboard`

---

## 🧪 Tests

```bash
# Ejecutar todos los tests
php artisan test

# Ejecutar tests de autorización
php artisan test --filter AuthorizationTest

# Con cobertura
php artisan test --coverage
```

**Tests Implementados**:
- ✅ Tests de autorización (20+ tests)
- ✅ Tests de funcionalidades
- ✅ Tests de integración

---

## 📚 Documentación

Documentación completa disponible en la carpeta `docs/`:

- **[MODELO_CONCEPTUAL_ARQUITECTONICO.md](docs/MODELO_CONCEPTUAL_ARQUITECTONICO.md)** - Arquitectura y modelo de datos
- **[IMPLEMENTACION_COMPLETA.md](docs/IMPLEMENTACION_COMPLETA.md)** - Detalles de implementación
- **[CORRECCION_MODELO_ROLES.md](docs/CORRECCION_MODELO_ROLES.md)** - Sistema de roles y permisos
- **[AJUSTES_FINOS.md](docs/AJUSTES_FINOS.md)** - Ajustes y correcciones

---

## 🔧 Comandos Útiles

```bash
# Desarrollo
composer dev                    # Inicia servidor, queue, logs y vite
npm run dev                     # Compilar assets en desarrollo
npm run build                   # Compilar para producción

# Base de datos
php artisan migrate             # Ejecutar migraciones
php artisan migrate:fresh       # Resetear y ejecutar migraciones
php artisan db:seed             # Ejecutar seeders

# Cache
php artisan config:clear        # Limpiar configuración
php artisan cache:clear         # Limpiar cache
php artisan route:clear         # Limpiar rutas

# Ziggy (rutas JS)
php artisan ziggy:generate      # Regenerar rutas para JavaScript
```

---

## 🏗️ Arquitectura

### Modelo de Datos

```
Super Admin (is_super_admin)
    ↓
Usuarios
    ↓
Equipos (Teams)
    ├── Owner (owner_id)
    ├── Admin (team_user.role)
    ├── Member (team_user.role)
    └── Viewer (team_user.role)
    ↓
Proyectos (Projects)
    ├── Owner (owner_id)
    ├── Admin (project_user.role)
    ├── Editor (project_user.role)
    └── Viewer (project_user.role)
    ↓
Tareas (Tasks)
    └── Estados (TaskStatus)
```

### Principios de Diseño

- **Policies**: Autorización basada en Laravel Policies
- **Service Layer**: Lógica de negocio en servicios (PermissionService, etc.)
- **Enums**: Type safety para roles (TeamRole, ProjectRole)
- **Inertia.js**: SPA sin API REST
- **WebSockets**: Colaboración en tiempo real

---

## 🔐 Seguridad

- ✅ Autenticación con Laravel Breeze
- ✅ Verificación de email
- ✅ Policies de autorización granulares
- ✅ Validación de permisos en backend
- ✅ CSRF protection
- ✅ SQL injection protection (Eloquent)
- ✅ XSS protection (Vue 3)

---

## 📊 Estado del Proyecto

✅ **MVP Completado** - Todas las funcionalidades principales implementadas  
🚧 **En evolución continua** - Features avanzadas en desarrollo

### Funcionalidades Implementadas

- ✅ Sistema de usuarios y autenticación
- ✅ Gestión de equipos y proyectos
- ✅ Sistema de permisos y roles
- ✅ Panel de Super Admin
- ✅ Dashboard con métricas
- ✅ Tests de autorización
- ✅ Documentación completa

### En Desarrollo

- 🚧 Colaboración en tiempo real avanzada
- 🚧 Kanban board con drag & drop
- 🚧 Sistema de comentarios mejorado
- 🚧 Gestión avanzada de archivos

---

## 🤝 Contribuir

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

---

## 📝 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

---

## 👨‍💻 Autor

**Leranuva**

- GitHub: [@leranuva](https://github.com/leranuva)

---

## 🙏 Agradecimientos

- [Laravel](https://laravel.com) - Framework PHP
- [Vue.js](https://vuejs.org) - Framework JavaScript
- [Inertia.js](https://inertiajs.com) - SPA framework
- [Tailwind CSS](https://tailwindcss.com) - Framework CSS
- [Chart.js](https://www.chartjs.org) - Gráficos

---


