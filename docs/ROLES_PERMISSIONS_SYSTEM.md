# 🔐 Sistema de Roles y Permisos

**Fecha**: Diciembre 2025  
**Estado**: ✅ **IMPLEMENTADO**

---

## 📋 Resumen

Sistema completo de roles y permisos con control granular por proyecto y bloqueo por dependencias.

---

## 🎭 Roles Implementados

### Roles Globales

| Rol | Slug | Descripción | Permisos |
|-----|------|-------------|----------|
| **Super Admin** | `super-admin` | Acceso completo al sistema | Todos los permisos |
| **Admin** | `admin` | Administrador del sistema | Gestión completa (sin eliminar equipos) |
| **Manager** | `manager` | Gestor de proyectos y equipos | Gestión de proyectos y tareas |
| **Member** | `member` | Miembro estándar | Crear y editar tareas, comentarios |
| **Viewer** | `viewer` | Solo lectura | Ver contenido |

### Roles por Equipo

| Rol | Slug | Descripción | Permisos |
|-----|------|-------------|----------|
| **Team Admin** | `team-admin` | Administrador del equipo | Gestión completa del equipo |
| **Team Editor** | `team-editor` | Editor del equipo | Crear y editar proyectos |
| **Team Viewer** | `team-viewer` | Visualizador del equipo | Solo lectura |

### Roles por Proyecto

| Rol | Slug | Descripción | Permisos |
|-----|------|-------------|----------|
| **Project Admin** | `project-admin` | Administrador del proyecto | Gestión completa del proyecto |
| **Project Editor** | `project-editor` | Editor del proyecto | Crear y editar tareas |
| **Project Viewer** | `project-viewer` | Visualizador del proyecto | Solo lectura |

---

## 🔑 Permisos Implementados

### Teams
- `teams.view` - Ver equipos
- `teams.create` - Crear equipos
- `teams.update` - Actualizar equipos
- `teams.delete` - Eliminar equipos
- `teams.manage_members` - Gestionar miembros del equipo

### Projects
- `projects.view` - Ver proyectos
- `projects.create` - Crear proyectos
- `projects.update` - Actualizar proyectos
- `projects.delete` - Eliminar proyectos
- `projects.manage_members` - Gestionar miembros del proyecto
- `projects.manage_settings` - Gestionar configuración del proyecto

### Tasks
- `tasks.view` - Ver tareas
- `tasks.create` - Crear tareas
- `tasks.update` - Actualizar tareas
- `tasks.delete` - Eliminar tareas
- `tasks.assign` - Asignar tareas
- `tasks.move` - Mover tareas en Kanban

### Comments
- `comments.view` - Ver comentarios
- `comments.create` - Crear comentarios
- `comments.update` - Actualizar comentarios
- `comments.delete` - Eliminar comentarios

### Files
- `files.upload` - Subir archivos
- `files.download` - Descargar archivos
- `files.delete` - Eliminar archivos

### System
- `system.manage_users` - Gestionar usuarios del sistema
- `system.manage_roles` - Gestionar roles y permisos

---

## 🏗️ Arquitectura

### Componentes

1. **PermissionService** (`app/Services/PermissionService.php`)
   - Servicio centralizado para verificación de permisos
   - Lógica de herencia de permisos (global → team → project)
   - Verificación de bloqueo por dependencias

2. **Policies** (`app/Policies/`)
   - `TeamPolicy` - Autorización de equipos
   - `ProjectPolicy` - Autorización de proyectos
   - `TaskPolicy` - Autorización de tareas (con bloqueo por dependencias)
   - `CommentPolicy` - Autorización de comentarios

3. **Gates** (registrados en `AppServiceProvider`)
   - `manage-teams` - Gestionar equipos
   - `manage-projects` - Gestionar proyectos
   - `manage-tasks` - Gestionar tareas
   - `move-task` - Mover tareas (con verificación de dependencias)

4. **Middleware** (`app/Http/Middleware/CheckPermission.php`)
   - Middleware para verificar permisos en rutas
   - Soporte para permisos globales, de equipo y de proyecto

5. **Trait HasPermissions** (`app/Traits/HasPermissions.php`)
   - Métodos helper en el modelo User
   - Facilita la verificación de permisos

---

## 🔄 Jerarquía de Permisos

```
Super Admin (todos los permisos)
    ↓
Global Roles (Admin, Manager, Member, Viewer)
    ↓
Team Roles (Team Admin, Team Editor, Team Viewer)
    ↓
Project Roles (Project Admin, Project Editor, Project Viewer)
    ↓
Owner (siempre tiene permisos)
```

**Reglas de herencia:**
- Super Admin tiene acceso a todo
- Permisos globales se aplican a todo el sistema
- Permisos de equipo se aplican a todos los proyectos del equipo
- Permisos de proyecto se aplican solo al proyecto específico
- Owner siempre tiene permisos completos en su recurso

---

## 🚫 Bloqueo por Dependencias

### Implementación

El sistema verifica dependencias bloqueantes antes de permitir mover tareas:

```php
// En TaskPolicy::move()
public function move(User $user, Task $task, $newStatusId): bool
{
    // Verificar permiso básico
    if (!$this->permissionService->hasProjectPermission($user, $task->project, 'tasks.move')) {
        return false;
    }

    // Verificar dependencias bloqueantes
    return $this->permissionService->canMoveTask($user, $task, $newStatusId);
}
```

### Lógica de Bloqueo

1. **Verificar permiso**: Usuario debe tener `tasks.move`
2. **Verificar dependencias**: Tarea no debe tener dependencias bloqueantes sin completar
3. **Tipo de dependencia**: Solo dependencias tipo `blocks` bloquean el movimiento

### Métodos en Task Model

```php
// Verificar si tiene dependencias bloqueantes
$task->hasBlockingDependencies();

// Obtener dependencias bloqueantes
$task->getBlockingDependencies();

// Verificar si puede moverse a un estado
$task->canMoveToStatus($newStatusId);
```

---

## 📝 Uso del Sistema

### En Controllers

```php
// Usar Policies
$this->authorize('view', $project);
$this->authorize('create', Task::class);
$this->authorize('move', [$task, $newStatusId]);

// Usar Gates
Gate::authorize('manage-projects', $project);
Gate::authorize('move-task', [$task, $newStatusId]);
```

### En Middleware

```php
// En routes/web.php
Route::middleware(['auth', 'permission:tasks.create,project,{project}'])->group(function () {
    Route::post('/projects/{project}/tasks', [TaskController::class, 'store']);
});
```

### En Blade/Vue

```php
// Verificar permisos
@can('update', $project)
    <button>Editar Proyecto</button>
@endcan

@can('move', [$task, $newStatusId])
    <button>Mover Tarea</button>
@endcan
```

### En Modelos

```php
// Usar trait HasPermissions
$user->hasPermission('tasks.create');
$user->hasTeamPermission($team, 'projects.create');
$user->hasProjectPermission($project, 'tasks.update');
$user->isSuperAdmin();
$user->isAdmin();
```

---

## 🔧 Configuración

### Registro de Policies

Las policies están registradas en `app/Providers/AuthServiceProvider.php`:

```php
protected $policies = [
    Team::class => TeamPolicy::class,
    Project::class => ProjectPolicy::class,
    Task::class => TaskPolicy::class,
    Comment::class => CommentPolicy::class,
];
```

### Registro de Gates

Los gates están registrados en `app/Providers/AppServiceProvider.php`:

```php
Gate::define('manage-teams', ...);
Gate::define('manage-projects', ...);
Gate::define('manage-tasks', ...);
Gate::define('move-task', ...);
```

### Registro de Middleware

El middleware está registrado en `bootstrap/app.php`:

```php
$middleware->alias([
    'permission' => \App\Http\Middleware\CheckPermission::class,
]);
```

---

## 📊 Asignación de Permisos a Roles

### Super Admin
- ✅ Todos los permisos

### Admin
- ✅ Gestión de equipos, proyectos, tareas
- ✅ Gestión de comentarios y archivos
- ❌ Eliminar equipos (solo Super Admin)

### Manager
- ✅ Ver equipos
- ✅ Gestión de proyectos y tareas
- ✅ Crear y editar comentarios
- ❌ Eliminar proyectos

### Member
- ✅ Ver equipos y proyectos
- ✅ Crear y editar tareas
- ✅ Crear y editar comentarios
- ✅ Subir y descargar archivos
- ❌ Eliminar tareas

### Viewer
- ✅ Ver contenido
- ✅ Descargar archivos
- ❌ Crear, editar o eliminar

---

## ✅ Checklist de Implementación

- [x] Roles globales creados
- [x] Roles por equipo creados
- [x] Roles por proyecto creados
- [x] Permisos base creados
- [x] Asignación de permisos a roles
- [x] PermissionService implementado
- [x] Policies implementadas
- [x] Gates implementados
- [x] Middleware implementado
- [x] Trait HasPermissions implementado
- [x] Bloqueo por dependencias implementado
- [x] Control granular por proyecto implementado

---

## 🎯 Próximos Pasos

1. **Crear helpers adicionales**
   - Métodos para asignar roles a usuarios
   - Métodos para verificar roles en contexto

2. **Implementar en Controllers**
   - Usar Policies en todos los métodos
   - Validar permisos antes de acciones

3. **Frontend**
   - Mostrar/ocultar botones según permisos
   - Validar permisos antes de acciones

4. **Testing**
   - Tests unitarios para PermissionService
   - Tests de integración para Policies

---

**Última actualización**: Diciembre 2025  
**Estado**: ✅ **COMPLETO**

