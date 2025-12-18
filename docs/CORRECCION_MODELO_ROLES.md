# 🔧 Corrección del Modelo de Roles
## Eliminación de Ambigüedades y Simplificación

**Fecha**: Diciembre 2025  
**Versión**: 2.0.0  
**Estado**: ✅ **CORREGIDO**

---

## 🚨 Problemas Identificados y Corregidos

### Problema #1: Roles Globales Mal Definidos

**❌ ANTES:**
- Roles globales: Admin, Manager, Member, Viewer
- Relación polimórfica `roles(): MorphToMany`
- Métodos ambiguos: `isAdmin()`, `hasRole()`

**✅ DESPUÉS:**
- **Solo Super Admin** como rol global
- Campo `is_super_admin` (boolean) en tabla `users`
- Sin roles polimórficos
- Métodos claros: `isSuperAdmin()`, `hasTeamRole()`, `hasProjectRole()`

### Problema #2: Owner Confundido con Rol

**❌ ANTES:**
- Owner tratado como rol más en la jerarquía
- Owner en la tabla de roles

**✅ DESPUÉS:**
- **Owner es una regla especial**, NO un rol
- Owner se verifica por `team.owner_id` o `project.owner_id`
- Owner siempre retorna `true` en verificaciones de permisos
- Owner no se almacena en tablas pivote

### Problema #3: Roles Polimórficos Innecesarios

**❌ ANTES:**
```php
public function roles(): MorphToMany
{
    return $this->morphToMany(Role::class, 'roleable', 'role_user');
}
```

**✅ DESPUÉS:**
```php
// Eliminado completamente
// Roles solo existen en:
// - team_user.role (string: 'admin', 'member', 'viewer')
// - project_user.role (string: 'admin', 'editor', 'viewer')
```

### Problema #4: Métodos Ambiguos

**❌ ANTES:**
```php
public function isAdmin(): bool
{
    return $this->hasRole('admin') || $this->isSuperAdmin();
}
// ¿Admin de qué? ¿Sistema? ¿Equipo? ¿Proyecto?
```

**✅ DESPUÉS:**
```php
// Eliminado isAdmin()
// Métodos específicos:
public function hasTeamRole(Team $team, string $role): bool
public function hasProjectRole(Project $project, string $role): bool
public function isSuperAdmin(): bool
```

---

## 📊 Modelo Corregido

### Estructura de Datos

```
users
├── id
├── name
├── email
├── password
├── is_super_admin (boolean) ← ÚNICO rol global
└── email_verified_at

teams
├── id
├── name
├── owner_id ← Owner (regla especial)
└── ...

team_user (pivote)
├── team_id
├── user_id
├── role (string: 'admin', 'member', 'viewer') ← Roles de equipo
└── joined_at

projects
├── id
├── name
├── team_id
├── owner_id ← Owner (regla especial)
└── ...

project_user (pivote)
├── project_id
├── user_id
├── role (string: 'admin', 'editor', 'viewer') ← Roles de proyecto
└── joined_at
```

### Jerarquía de Verificación

```
1. isSuperAdmin() → true → Acceso total
   ↓ (si false)
2. Es Owner? → true → Acceso total al recurso
   ↓ (si false)
3. Tiene rol en el recurso? → Verificar permisos del rol
   ↓ (si proyecto)
4. Tiene rol en el equipo? → Herencia de permisos
   ↓
5. Sin acceso → false
```

---

## 🔄 Cambios en el Código

### User Model

**Eliminado:**
- `roles(): MorphToMany`
- Relación con tabla `role_user`

**Agregado:**
- Campo `is_super_admin` en fillable y casts
- Métodos específicos: `hasTeamRole()`, `hasProjectRole()`, `getTeamRole()`, `getProjectRole()`

### HasPermissions Trait

**Eliminado:**
- `hasPermission()` (ambiguo)
- `hasRole()` (ambiguo)
- `isAdmin()` (ambiguo)
- `assignGlobalRole()` (innecesario)
- `assignTeamRole()` (innecesario, usar attach directo)
- `assignProjectRole()` (innecesario, usar attach directo)

**Mantenido/Mejorado:**
- `isSuperAdmin()` - Verifica `is_super_admin`
- `hasTeamRole(Team, string)` - Verifica rol en equipo
- `hasProjectRole(Project, string)` - Verifica rol en proyecto
- `hasTeamPermission()` - Verifica permisos en equipo
- `hasProjectPermission()` - Verifica permisos en proyecto

### PermissionService

**Eliminado:**
- `hasGlobalPermission()` - Solo Super Admin existe globalmente
- `hasGlobalRole()` - Solo Super Admin existe globalmente

**Mantenido:**
- `hasTeamPermission()` - Simplificado, sin verificar permisos globales
- `hasProjectPermission()` - Simplificado, con herencia de equipo
- `canMoveTask()` - Sin cambios

### Policies

**Actualizado:**
- `TeamPolicy` - Eliminadas referencias a `hasGlobalPermission()`
- `ProjectPolicy` - Eliminadas referencias a `hasGlobalPermission()`
- `TaskPolicy` - Eliminadas referencias a `hasGlobalPermission()`
- `CommentPolicy` - Eliminadas referencias a `hasGlobalPermission()`

**Lógica corregida:**
- Super Admin → `true` inmediatamente
- Owner → `true` inmediatamente
- Roles → Verificar según contexto (team/project)

---

## ✅ Beneficios de la Corrección

1. **Claridad Conceptual**
   - Un solo rol global (Super Admin)
   - Owner claramente definido como regla especial
   - Roles solo en contexto de Team/Project

2. **Menos Complejidad**
   - Sin relaciones polimórficas innecesarias
   - Sin métodos ambiguos
   - Código más fácil de entender y mantener

3. **Mejor Performance**
   - Menos joins en consultas
   - Verificaciones más directas
   - Menos tablas involucradas

4. **Menos Bugs**
   - Sin ambigüedad sobre qué rol se verifica
   - Lógica clara y predecible
   - Fácil de debuggear

---

## 📝 Migración de Datos

Si tienes datos existentes con roles globales:

```sql
-- Migrar usuarios con rol 'super-admin' a is_super_admin
UPDATE users 
SET is_super_admin = 1 
WHERE id IN (
    SELECT roleable_id 
    FROM role_user 
    WHERE roleable_type = 'App\Models\User' 
    AND role_id IN (
        SELECT id FROM roles WHERE slug = 'super-admin'
    )
);

-- Limpiar roles globales (opcional)
DELETE FROM role_user WHERE roleable_type = 'App\Models\User';
```

---

## 🎯 Modelo Final (Correcto)

```
User
├── is_super_admin (boolean) ← Único rol global
│
├── Team Roles (team_user.role)
│     ├── admin
│     ├── member
│     └── viewer
│
└── Project Roles (project_user.role)
      ├── admin
      ├── editor
      └── viewer

Owner (regla especial)
├── teams.owner_id
└── projects.owner_id
```

**Reglas:**
1. Super Admin → Acceso total (bypass)
2. Owner → Siempre tiene permisos en su recurso
3. Team Roles → Permisos en equipo y proyectos del equipo
4. Project Roles → Permisos solo en el proyecto
5. Sin rol → Sin acceso (excepto herencia del equipo)

---

**Última actualización**: Diciembre 2025  
**Estado**: ✅ **IMPLEMENTADO Y CORREGIDO**

