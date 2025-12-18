# 🏗️ Modelo Conceptual y Arquitectónico
## Sistema de Gestión de Usuarios, Equipos, Proyectos y Permisos
### Inspirado en Jira/ClickUp

**Fecha**: Diciembre 2025  
**Versión**: 2.0.0 (CORREGIDA)  
**Estado**: ✅ **IMPLEMENTADO Y CORREGIDO**

**⚠️ IMPORTANTE**: Este documento ha sido corregido para eliminar ambigüedades en el modelo de roles. Ver `CORRECCION_MODELO_ROLES.md` para detalles de los cambios.

---

## 📋 Tabla de Contenidos

1. [Resumen Ejecutivo](#resumen-ejecutivo)
2. [Modelo Conceptual](#modelo-conceptual)
3. [Arquitectura del Sistema](#arquitectura-del-sistema)
4. [Modelos de Datos](#modelos-de-datos)
5. [Sistema de Permisos](#sistema-de-permisos)
6. [Sistema de Invitaciones](#sistema-de-invitaciones)
7. [Flujos de Trabajo](#flujos-de-trabajo)
8. [API y Endpoints](#api-y-endpoints)
9. [Componentes Frontend](#componentes-frontend)
10. [Casos de Uso](#casos-de-uso)

---

## 🎯 Resumen Ejecutivo

Este documento describe el modelo conceptual y arquitectónico completo del sistema de gestión de usuarios, equipos, proyectos y permisos, diseñado siguiendo las mejores prácticas de herramientas como Jira y ClickUp.

### Características Principales

- ✅ **Gestión Completa de Usuarios**: CRUD con roles y permisos granulares
- ✅ **Sistema de Equipos**: Creación, gestión de miembros, roles y proyectos
- ✅ **Gestión de Proyectos**: Con herencia de permisos desde equipos
- ✅ **Sistema de Invitaciones**: Por email con tokens seguros y expiración
- ✅ **Permisos Heredados**: Equipo → Proyecto con jerarquía clara
- ✅ **Roles Granulares**: Owner, Admin, Editor, Member, Viewer en cada nivel
- ✅ **Políticas de Autorización**: Basadas en Laravel Policies

---

## 🧩 Modelo Conceptual

### Jerarquía del Sistema

```
Super Admin (Sistema) - Único rol global
    ↓
Usuarios
    ↓
Equipos (Teams)
    ├── Owner (regla especial, no es rol)
    ├── Admin (rol en team_user)
    ├── Member (rol en team_user)
    └── Viewer (rol en team_user)
    ↓
Proyectos (Projects)
    ├── Owner (regla especial, no es rol)
    ├── Admin (rol en project_user)
    ├── Editor (rol en project_user)
    └── Viewer (rol en project_user)
    ↓
Tareas (Tasks)
    └── Estados (TaskStatus)
```

**IMPORTANTE**: 
- **Super Admin** es el ÚNICO rol global (campo `is_super_admin` en users)
- **Owner** NO es un rol, es una regla especial que siempre tiene permisos
- Los roles (admin, member, viewer, editor) solo existen en el contexto de Team o Project
- NO hay roles polimórficos ni roles globales adicionales

### Relaciones Principales

```
User (1) ──< (N) Team (Owner)
User (N) ──< (N) Team (Members)
Team (1) ──< (N) Project
Project (1) ──< (N) Task
User (N) ──< (N) Project (Members)
User (1) ──< (N) Invitation (Invited By)
Invitation (N) ──> (1) Team/Project (Polimórfico)
```

### Conceptos Clave

1. **Usuario (User)**
   - Entidad central del sistema
   - Puede pertenecer a múltiples equipos y proyectos
   - **NO tiene roles globales** (excepto Super Admin mediante `is_super_admin`)
   - Tiene roles en equipos (almacenados en `team_user.role`)
   - Tiene roles en proyectos (almacenados en `project_user.role`)
   - Puede ser owner de equipos y proyectos (verificado por `owner_id`, NO es rol)

2. **Equipo (Team)**
   - Agrupación de usuarios para colaborar
   - Tiene un owner y múltiples miembros
   - Contiene múltiples proyectos
   - Los miembros heredan acceso a proyectos del equipo

3. **Proyecto (Project)**
   - Pertenece a un equipo
   - Tiene un owner y miembros directos
   - Los miembros del equipo pueden acceder automáticamente
   - Contiene tareas y estados

4. **Invitación (Invitation)**
   - Relación polimórfica con Team o Project
   - Token único para seguridad
   - Expiración automática (7 días)
   - Estado: pendiente, aceptada, expirada

---

## 🏛️ Arquitectura del Sistema

### Capas de la Aplicación

```
┌─────────────────────────────────────┐
│      Frontend (Vue 3 + Inertia)     │
│  - Components                        │
│  - Pages                             │
│  - Layouts                           │
└─────────────────────────────────────┘
                  ↕
┌─────────────────────────────────────┐
│      Backend (Laravel 12)           │
│  ┌───────────────────────────────┐  │
│  │  Controllers                  │  │
│  │  - UserController              │  │
│  │  - TeamController              │  │
│  │  - ProjectController           │  │
│  │  - InvitationController        │  │
│  └───────────────────────────────┘  │
│  ┌───────────────────────────────┐  │
│  │  Policies                     │  │
│  │  - UserPolicy                 │  │
│  │  - TeamPolicy                 │  │
│  │  - ProjectPolicy              │  │
│  └───────────────────────────────┘  │
│  ┌───────────────────────────────┐  │
│  │  Services                     │  │
│  │  - PermissionService          │  │
│  └───────────────────────────────┘  │
│  ┌───────────────────────────────┐  │
│  │  Models                       │  │
│  │  - User, Team, Project        │  │
│  │  - Invitation                 │  │
│  │  - Role, Permission          │  │
│  └───────────────────────────────┘  │
└─────────────────────────────────────┘
                  ↕
┌─────────────────────────────────────┐
│      Database (MySQL)               │
│  - users (con is_super_admin)       │
│  - teams, projects                  │
│  - team_user, project_user          │
│  - invitations                      │
│  ⚠️ NO hay tablas roles/permissions │
│     (roles son strings en pivots)   │
└─────────────────────────────────────┘
```

### Patrones de Diseño Utilizados

1. **Repository Pattern**: Implícito en Eloquent Models
2. **Service Layer**: PermissionService para lógica de negocio
3. **Policy Pattern**: Laravel Policies para autorización
4. **Form Request Validation**: Validación centralizada
5. **Polymorphic Relations**: Invitations con Team/Project

---

## 📊 Modelos de Datos

### User Model

```php
class User extends Authenticatable
{
    // Relaciones
    - teams(): BelongsToMany (con pivot.role)
    - ownedTeams(): HasMany
    - projects(): BelongsToMany (con pivot.role)
    - ownedProjects(): HasMany
    - invitations(): HasMany
    
    // Métodos (simplificados)
    - isSuperAdmin(): bool (verifica is_super_admin)
    - hasTeamRole(Team, string): bool
    - hasProjectRole(Project, string): bool
    - hasTeamPermission(Team, string): bool
    - hasProjectPermission(Project, string): bool
    - getTeamRole(Team): ?string
    - getProjectRole(Project): ?string
}
```

**Atributos:**
- `id`: Identificador único
- `name`: Nombre completo
- `email`: Correo electrónico (único)
- `password`: Contraseña hasheada
- `email_verified_at`: Fecha de verificación
- `is_super_admin`: Boolean - Único rol global del sistema

**❌ ELIMINADO:**
- `roles(): MorphToMany` - Roles polimórficos eliminados
- `isAdmin()` - Método ambiguo eliminado
- `hasRole()` - Método ambiguo eliminado

### Team Model

```php
class Team extends Model
{
    // Relaciones
    - owner(): BelongsTo (User)
    - users(): BelongsToMany (User)
    - projects(): HasMany (Project)
    - invitations(): MorphMany (Invitation)
    
    // Métodos
    - hasMember(User): bool
    - getMemberRole(User): ?string
}
```

**Atributos:**
- `id`: Identificador único
- `name`: Nombre del equipo
- `description`: Descripción opcional
- `slug`: URL amigable (único)
- `owner_id`: ID del propietario
- `avatar`: URL del avatar (opcional)
- `is_active`: Estado activo/inactivo

**Tabla Pivote `team_user`:**
- `team_id`: ID del equipo
- `user_id`: ID del usuario
- `role`: Rol en el equipo (admin, member, viewer)
- `joined_at`: Fecha de unión

### Project Model

```php
class Project extends Model
{
    // Relaciones
    - team(): BelongsTo (Team)
    - owner(): BelongsTo (User)
    - users(): BelongsToMany (User)
    - tasks(): HasMany (Task)
    - invitations(): MorphMany (Invitation)
    
    // Métodos
    - hasMember(User): bool
    - getMemberRole(User): ?string
}
```

**Atributos:**
- `id`: Identificador único
- `name`: Nombre del proyecto
- `description`: Descripción
- `slug`: URL amigable
- `team_id`: ID del equipo al que pertenece
- `owner_id`: ID del propietario
- `color`: Color del proyecto
- `icon`: Icono del proyecto
- `start_date`: Fecha de inicio
- `due_date`: Fecha de vencimiento
- `is_active`: Estado activo
- `is_archived`: Estado archivado

**Tabla Pivote `project_user`:**
- `project_id`: ID del proyecto
- `user_id`: ID del usuario
- `role`: Rol en el proyecto (admin, editor, viewer)
- `joined_at`: Fecha de unión

### Invitation Model

```php
class Invitation extends Model
{
    // Relaciones
    - invitable(): MorphTo (Team|Project)
    - invitedBy(): BelongsTo (User)
    
    // Métodos
    - isExpired(): bool
    - isAccepted(): bool
    - isValid(): bool
    - accept(): void
}
```

**Atributos:**
- `id`: Identificador único
- `email`: Correo electrónico del invitado
- `token`: Token único de 64 caracteres
- `invitable_type`: Tipo (Team o Project)
- `invitable_id`: ID del recurso
- `role`: Rol asignado
- `invited_by`: ID del usuario que invita
- `accepted_at`: Fecha de aceptación (nullable)
- `expires_at`: Fecha de expiración

---

## 🔐 Sistema de Permisos

### Jerarquía de Permisos (CORREGIDA)

```
Super Admin (is_super_admin = true)
    ↓ (bypass total, todos los permisos)
Owner (regla especial, NO es rol)
    ↓ (siempre tiene permisos en su recurso)
Team Roles (almacenados en team_user.role)
    ↓ (admin, member, viewer)
Project Roles (almacenados en project_user.role)
    ↓ (admin, editor, viewer)
Permisos específicos
```

**⚠️ IMPORTANTE:**
- Solo existe **UN rol global**: Super Admin (`is_super_admin`)
- **Owner NO es un rol**, es una regla especial verificada por `owner_id`
- Los roles adicionales solo existen en contexto de Team o Project
- NO hay roles polimórficos ni roles globales adicionales

### Roles Implementados

#### ⚠️ Roles Globales (SIMPLIFICADO)

| Rol | Implementación | Descripción | Permisos |
|-----|----------------|-------------|----------|
| **Super Admin** | `users.is_super_admin` (boolean) | Acceso completo al sistema | Todos los permisos, bypass total |

**❌ ELIMINADO:** Admin, Manager, Member, Viewer a nivel global (solo existen en Team/Project)

#### Roles de Equipo

**Almacenados en**: `team_user.role` (string)

| Rol | Descripción | Permisos | Notas |
|-----|-------------|----------|-------|
| **Owner** | Propietario del equipo | Gestión completa | ⚠️ NO es rol, es `teams.owner_id`. Siempre tiene permisos. |
| **Admin** | Administrador del equipo | Gestión de miembros y proyectos | Rol en `team_user.role` |
| **Member** | Miembro del equipo | Acceso a proyectos del equipo | Rol en `team_user.role` |
| **Viewer** | Visualizador del equipo | Solo lectura del equipo | Rol en `team_user.role` |

#### Roles de Proyecto

**Almacenados en**: `project_user.role` (string)

| Rol | Descripción | Permisos | Notas |
|-----|-------------|----------|-------|
| **Owner** | Propietario del proyecto | Gestión completa | ⚠️ NO es rol, es `projects.owner_id`. Siempre tiene permisos. |
| **Admin** | Administrador del proyecto | Gestión de miembros y tareas | Rol en `project_user.role` |
| **Editor** | Editor del proyecto | Crear y editar tareas | Rol en `project_user.role` |
| **Viewer** | Visualizador del proyecto | Solo lectura | Rol en `project_user.role` |

### Permisos Implementados

#### Teams
- `teams.view` - Ver equipos
- `teams.create` - Crear equipos
- `teams.update` - Actualizar equipos
- `teams.delete` - Eliminar equipos
- `teams.manage_members` - Gestionar miembros

#### Projects
- `projects.view` - Ver proyectos
- `projects.create` - Crear proyectos
- `projects.update` - Actualizar proyectos
- `projects.delete` - Eliminar proyectos
- `projects.manage_members` - Gestionar miembros
- `projects.manage_settings` - Gestionar configuración

#### Tasks
- `tasks.view` - Ver tareas
- `tasks.create` - Crear tareas
- `tasks.update` - Actualizar tareas
- `tasks.delete` - Eliminar tareas
- `tasks.assign` - Asignar tareas
- `tasks.move` - Mover tareas

### Herencia de Permisos (CORREGIDA)

El sistema implementa herencia de permisos siguiendo esta lógica:

1. **Super Admin** (`is_super_admin = true`) → Acceso total, bypass de todas las verificaciones
2. **Owner** (verificado por `owner_id`) → Siempre tiene permisos completos en su recurso
3. **Permisos de Equipo** → Se aplican a todos los proyectos del equipo (herencia)
4. **Permisos de Proyecto** → Se aplican solo al proyecto específico
5. **Sin rol** → Sin acceso (excepto si es miembro del equipo que contiene el proyecto)

**Ejemplo de Herencia:**

```
Usuario con rol "admin" en Equipo "Desarrollo" (team_user.role = 'admin')
    ↓
Acceso automático a todos los proyectos del equipo
    ↓
Puede gestionar miembros de proyectos (según permisos del rol admin)
    ↓
Puede crear y editar tareas (según permisos del rol admin)
```

**Reglas de Herencia:**
- Los miembros del equipo heredan acceso a proyectos del equipo
- Los permisos específicos se verifican según el rol en el equipo
- Los roles directos en proyectos tienen prioridad sobre la herencia del equipo

### PermissionService (SIMPLIFICADO)

El servicio centralizado `PermissionService` maneja toda la lógica de verificación de permisos:

```php
class PermissionService
{
    - hasTeamPermission(User, Team, string): bool
    - hasProjectPermission(User, Project, string): bool
    - canMoveTask(User, Task, int): bool
}
```

**❌ ELIMINADO:**
- `hasGlobalPermission()` - Ya no existe, solo Super Admin
- `hasGlobalRole()` - Ya no existe, solo `is_super_admin`

**Lógica de Verificación (CORREGIDA):**

1. **Super Admin** (`user->isSuperAdmin()`) → Acceso total, retorna `true` inmediatamente
2. **Owner del recurso** → Siempre tiene permisos, retorna `true`
3. **Rol en el recurso** → Verifica permisos según el rol (admin, member, viewer, editor)
4. **Herencia** → Si es proyecto, verifica permisos del equipo padre
5. **Sin acceso** → Retorna `false`

**Mapeo de Roles a Permisos:**

Los permisos se mapean directamente desde los roles en las tablas pivote:
- `team_user.role` → Permisos del equipo
- `project_user.role` → Permisos del proyecto

---

## 📧 Sistema de Invitaciones

### Flujo de Invitación

```
1. Usuario con permisos invita a un email
   ↓
2. Se crea Invitation con token único
   ↓
3. Se envía email con link de aceptación
   ↓
4. Usuario hace clic en el link
   ↓
5. Si no tiene cuenta → Redirige a registro
   Si tiene cuenta → Redirige a login
   ↓
6. Usuario acepta la invitación
   ↓
7. Se agrega al recurso (Team/Project)
   ↓
8. Invitation se marca como aceptada
```

### Características

- **Tokens Únicos**: 64 caracteres aleatorios
- **Expiración**: 7 días por defecto
- **Polimórfico**: Funciona con Teams y Projects
- **Estado**: Pendiente, Aceptada, Expirada
- **Validación**: Evita duplicados y verifica permisos

### Endpoints de Invitaciones

```
POST   /invitations/teams/{team}        - Invitar a equipo
POST   /invitations/projects/{project}  - Invitar a proyecto
GET    /invitations/accept/{token}      - Aceptar invitación
POST   /invitations/reject/{token}     - Rechazar invitación
DELETE /invitations/{invitation}        - Cancelar invitación
```

---

## 🔄 Flujos de Trabajo

### Crear y Gestionar Equipo

```
1. Usuario crea equipo
   ↓
2. Usuario se convierte en Owner automáticamente
   ↓
3. Owner invita miembros por email
   ↓
4. Miembros aceptan invitaciones
   ↓
5. Owner asigna roles a miembros
   ↓
6. Miembros pueden crear proyectos en el equipo
```

### Crear Proyecto en Equipo

```
1. Miembro del equipo crea proyecto
   ↓
2. Proyecto se asocia al equipo
   ↓
3. Miembros del equipo heredan acceso
   ↓
4. Owner del proyecto puede invitar miembros directos
   ↓
5. Miembros directos tienen roles específicos
```

### Gestión de Miembros

**En Equipos:**
- Owner puede agregar/eliminar miembros
- Owner puede cambiar roles
- Admin puede gestionar miembros (excepto owner)
- No se puede eliminar al owner

**En Proyectos:**
- Owner puede agregar/eliminar miembros
- Owner puede cambiar roles
- Admin puede gestionar miembros (excepto owner)
- Miembros del equipo aparecen automáticamente

---

## 🌐 API y Endpoints

### Equipos (Teams)

```
GET    /teams                    - Listar equipos
GET    /teams/create             - Formulario crear
POST   /teams                    - Crear equipo
GET    /teams/{team}             - Ver equipo
GET    /teams/{team}/edit        - Formulario editar
PUT    /teams/{team}             - Actualizar equipo
DELETE /teams/{team}             - Eliminar equipo
GET    /teams/{team}/members     - Listar miembros
POST   /teams/{team}/members     - Agregar miembro
PUT    /teams/{team}/members/{user} - Actualizar rol
DELETE /teams/{team}/members/{user} - Eliminar miembro
```

### Proyectos (Projects)

```
GET    /projects                         - Listar proyectos
GET    /projects/create                  - Formulario crear
POST   /projects                         - Crear proyecto
GET    /projects/{project}               - Ver proyecto
GET    /projects/{project}/edit         - Formulario editar
PUT    /projects/{project}               - Actualizar proyecto
DELETE /projects/{project}               - Eliminar proyecto
GET    /projects/{project}/members       - Listar miembros
POST   /projects/{project}/members        - Agregar miembro
PUT    /projects/{project}/members/{user} - Actualizar rol
DELETE /projects/{project}/members/{user} - Eliminar miembro
```

### Invitaciones (Invitations)

```
POST   /invitations/teams/{team}        - Invitar a equipo
POST   /invitations/projects/{project}  - Invitar a proyecto
GET    /invitations/accept/{token}      - Aceptar invitación
POST   /invitations/reject/{token}      - Rechazar invitación
DELETE /invitations/{invitation}        - Cancelar invitación
```

### Usuarios (Users) - Solo Super Admin

```
GET    /users                    - Listar usuarios
GET    /users/create             - Formulario crear
POST   /users                    - Crear usuario
GET    /users/{user}             - Ver usuario
GET    /users/{user}/edit        - Formulario editar
PUT    /users/{user}             - Actualizar usuario
DELETE /users/{user}             - Eliminar usuario
```

---

## 🎨 Componentes Frontend

### Equipos

#### Teams/Index.vue
- Lista de equipos con búsqueda
- Cards con información del equipo
- Enlace para crear nuevo equipo
- Paginación

#### Teams/Create.vue
- Formulario para crear equipo
- Validación en tiempo real
- Campos: nombre, descripción

#### Teams/Show.vue
- Vista detallada del equipo
- Lista de miembros con gestión
- Lista de proyectos del equipo
- Modal para invitar miembros
- Gestión de roles de miembros
- Invitaciones pendientes

### Proyectos

#### Projects/Show.vue (Mejorado)
- Vista detallada del proyecto
- Gestión de miembros
- Herencia de miembros del equipo
- Invitaciones a proyecto

### Usuarios

#### Users/Index.vue
- Lista de usuarios (solo super admin)
- Búsqueda y filtros
- Gestión de Super Admin (activar/desactivar flag)

#### Users/Create.vue
- Formulario para crear usuario
- Toggle para Super Admin (checkbox)

#### Users/Edit.vue
- Editar usuario existente
- Cambiar contraseña opcional
- Toggle para Super Admin (checkbox)

---

## 📝 Casos de Uso

### Caso 1: Crear Equipo y Agregar Miembros

**Actor**: Usuario con permiso `teams.create`

**Flujo:**
1. Usuario accede a `/teams/create`
2. Completa formulario (nombre, descripción)
3. Sistema crea equipo y asigna como owner
4. Usuario invita miembros por email
5. Sistema envía invitaciones
6. Miembros aceptan y se agregan al equipo

**Resultado**: Equipo creado con miembros

### Caso 2: Crear Proyecto en Equipo

**Actor**: Miembro del equipo con permiso `projects.create`

**Flujo:**
1. Usuario accede a `/projects/create?team_id={id}`
2. Completa formulario del proyecto
3. Sistema crea proyecto asociado al equipo
4. Todos los miembros del equipo heredan acceso
5. Owner del proyecto puede invitar miembros directos

**Resultado**: Proyecto creado con acceso heredado

### Caso 3: Gestionar Permisos de Miembro

**Actor**: Owner o Admin del equipo/proyecto

**Flujo:**
1. Usuario accede a gestión de miembros
2. Selecciona miembro a modificar
3. Cambia rol (admin, member, viewer)
4. Sistema actualiza permisos inmediatamente
5. Miembro ve cambios en tiempo real

**Resultado**: Permisos actualizados

### Caso 4: Invitación por Email

**Actor**: Usuario con permisos de gestión de miembros

**Flujo:**
1. Usuario ingresa email y rol
2. Sistema crea invitación con token único
3. Sistema envía email con link
4. Usuario invitado hace clic en link
5. Si no tiene cuenta, se registra
6. Si tiene cuenta, inicia sesión
7. Sistema agrega usuario al recurso
8. Invitación se marca como aceptada

**Resultado**: Usuario agregado al equipo/proyecto

---

## 🔒 Seguridad

### Medidas Implementadas

1. **Autorización por Policies**: Todas las acciones verifican permisos
2. **Tokens Únicos**: Invitaciones usan tokens de 64 caracteres
3. **Expiración**: Invitaciones expiran en 7 días
4. **Validación**: Form Requests validan todos los inputs
5. **Protección de Owners**: No se pueden eliminar owners
6. **Verificación de Duplicados**: Evita miembros duplicados
7. **CSRF Protection**: Laravel protege contra CSRF

### Reglas de Negocio

- Solo Super Admin puede gestionar usuarios del sistema
- Solo Owner puede eliminar equipo/proyecto
- No se puede eliminar al owner de un recurso
- No se pueden tener miembros duplicados
- Las invitaciones expiran automáticamente
- Los permisos se heredan: Equipo → Proyecto

---

## 📈 Mejoras Futuras

1. **Notificaciones en Tiempo Real**: WebSockets para invitaciones
2. **Emails de Invitación**: Implementar envío real de emails
3. **Auditoría**: Log de cambios en miembros y permisos
4. **Roles Personalizados**: Permitir crear roles custom
5. **Permisos Granulares**: Más granularidad en permisos
6. **Bulk Operations**: Invitar múltiples usuarios a la vez
7. **Exportación**: Exportar listas de miembros
8. **Analytics**: Estadísticas de uso por equipo/proyecto

---

## ✅ Checklist de Implementación

### Backend
- [x] Modelos (User, Team, Project, Invitation)
- [x] Migraciones de base de datos
- [x] Controladores (UserController, TeamController, InvitationController)
- [x] Policies (UserPolicy, TeamPolicy, ProjectPolicy)
- [x] Form Requests de validación
- [x] PermissionService mejorado
- [x] Rutas completas
- [x] Relaciones polimórficas

### Frontend
- [x] Teams/Index.vue
- [x] Teams/Create.vue
- [x] Teams/Show.vue
- [x] Users/Index.vue
- [x] Users/Create.vue
- [x] Users/Edit.vue
- [x] Enlaces en AppLayout

### Pendiente
- [ ] Mejorar Projects/Show.vue con gestión de miembros
- [ ] Componente de invitaciones pendientes
- [ ] Emails de invitación
- [ ] Notificaciones en tiempo real

---

## 📚 Referencias

- [Laravel Policies](https://laravel.com/docs/authorization#creating-policies)
- [Laravel Relationships](https://laravel.com/docs/eloquent-relationships)
- [Inertia.js](https://inertiajs.com/)
- [Vue 3](https://vuejs.org/)

---

**Última actualización**: Diciembre 2025  
**Autor**: Sistema de Gestión de Tareas  
**Versión del Documento**: 1.0.0

