# Task Manager Beta - Estructura del Proyecto

## 📋 Descripción del Proyecto

Plataforma de gestión de proyectos colaborativa en tiempo real, similar a Jira/ClickUp, desarrollada con:
- **Backend**: Laravel 12
- **Frontend**: Vue 3 + Inertia.js (por instalar)
- **WebSockets**: Laravel Reverb
- **Base de datos**: MySQL (XAMPP)
- **Cache/Queue**: Redis

## 🎯 Alcance MVP

### ✅ Completado

1. **Base de datos y modelos**
   - ✅ Tabla `teams` - Equipos de trabajo
   - ✅ Tabla `projects` - Proyectos dentro de equipos
   - ✅ Tabla `tasks` - Tareas del proyecto
   - ✅ Tabla `task_statuses` - Estados para Kanban
   - ✅ Tabla `roles` - Roles del sistema
   - ✅ Tabla `permissions` - Permisos
   - ✅ Tabla `notifications` - Notificaciones
   - ✅ Tablas pivot: `team_user`, `project_user`, `role_user`, `permission_role`

2. **Modelos Eloquent**
   - ✅ `User` - Usuarios con relaciones
   - ✅ `Team` - Equipos
   - ✅ `Project` - Proyectos
   - ✅ `Task` - Tareas
   - ✅ `TaskStatus` - Estados de tareas
   - ✅ `Role` - Roles
   - ✅ `Permission` - Permisos

3. **Configuración**
   - ✅ Laravel 12 instalado
   - ✅ Laravel Breeze (autenticación)
   - ✅ Laravel Reverb (WebSockets)
   - ✅ MySQL configurado
   - ✅ Redis configurado

### 🔄 Pendiente

1. **Frontend**
   - ⏳ Instalar Vue 3 + Inertia.js
   - ⏳ Configurar Vite para Vue
   - ⏳ Crear componentes base

2. **Autenticación y usuarios**
   - ⏳ Mejorar registro/login (ya con Breeze)
   - ⏳ Perfiles de usuario
   - ⏳ Gestión de equipos

3. **Proyectos y equipos**
   - ⏳ CRUD de equipos
   - ⏳ CRUD de proyectos
   - ⏳ Invitaciones a equipos/proyectos

4. **Tareas con Kanban**
   - ⏳ Board Kanban
   - ⏳ Drag & drop de tareas
   - ⏳ Crear/editar/eliminar tareas
   - ⏳ Estados personalizados por proyecto

5. **Roles y permisos**
   - ⏳ Sistema de permisos
   - ⏳ Asignación de roles
   - ⏳ Middleware de permisos

6. **Colaboración en tiempo real**
   - ⏳ Eventos de broadcasting
   - ⏳ Actualizaciones en tiempo real del Kanban
   - ⏳ Notificaciones en tiempo real

7. **Notificaciones**
   - ⏳ Sistema de notificaciones
   - ⏳ Notificaciones push
   - ⏳ Centro de notificaciones

## 📊 Estructura de Base de Datos

### Relaciones Principales

```
User
├── owns Teams (owner_id)
├── belongs to Teams (team_user)
├── owns Projects (owner_id)
├── belongs to Projects (project_user)
├── assigned Tasks (assigned_to)
├── created Tasks (created_by)
└── has Roles (role_user)

Team
├── has Projects
├── belongs to Owner (User)
└── has Users (team_user)

Project
├── belongs to Team
├── belongs to Owner (User)
├── has Tasks
├── has TaskStatuses
└── has Users (project_user)

Task
├── belongs to Project
├── belongs to Status (TaskStatus)
├── belongs to AssignedTo (User)
└── belongs to CreatedBy (User)

TaskStatus
├── belongs to Project
└── has Tasks

Role
├── has Permissions (permission_role)
└── has Users (role_user)

Permission
└── has Roles (permission_role)
```

## 🚀 Próximos Pasos

1. **Instalar Vue 3 + Inertia.js**
   ```bash
   composer require inertiajs/inertia-laravel
   npm install @inertiajs/vue3 vue@^3
   ```

2. **Crear seeders para datos iniciales**
   - Roles y permisos por defecto
   - Estados de tareas por defecto

3. **Desarrollar API/Controllers**
   - TeamController
   - ProjectController
   - TaskController
   - KanbanController

4. **Implementar frontend**
   - Layout principal
   - Dashboard
   - Kanban board
   - Formularios

5. **Configurar eventos y broadcasting**
   - TaskCreated
   - TaskUpdated
   - TaskMoved
   - UserJoined

## 📝 Notas Técnicas

- **WebSockets**: Reverb configurado en puerto 8080
- **Redis**: Requerido para cache, queue y broadcasting
- **MySQL**: Base de datos `task_manager_beta`
- **Autenticación**: Laravel Breeze con Blade (cambiar a Inertia)

## 🔧 Comandos Útiles

```bash
# Iniciar servidor Laravel
php artisan serve

# Iniciar servidor Reverb (WebSockets)
php artisan reverb:start

# Ejecutar migraciones
php artisan migrate

# Crear seeder
php artisan make:seeder RoleSeeder
```

