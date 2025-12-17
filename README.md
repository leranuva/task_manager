# Task Manager Beta

> Plataforma de gestión de proyectos colaborativa en tiempo real

## 📚 Documentación Completa

Para ver la documentación completa del proyecto, consulta:
- **[Documentación Completa](./docs/PROJECT_DOCUMENTATION.md)** - Guía completa de todas las funcionalidades implementadas

## 🚀 Inicio Rápido

Plataforma de gestión de proyectos colaborativa en tiempo real, similar a Jira/ClickUp.

## 🚀 Tecnologías

- **Backend**: Laravel 12
- **Frontend**: Vue 3 + Inertia.js (pendiente)
- **WebSockets**: Laravel Reverb
- **Base de datos**: MySQL/MariaDB
- **Cache/Queue**: Redis

## 📋 Requisitos

- PHP >= 8.2
- Composer >= 2.0
- Node.js >= 18
- MySQL >= 8.0 (o MariaDB compatible)
- Redis >= 6.0 (recomendado)

## 🛠️ Instalación

1. **Clonar el repositorio**
   ```bash
   git clone <repository-url>
   cd task_manager_beta
   ```

2. **Instalar dependencias PHP**
   ```bash
   composer install
   ```

3. **Instalar dependencias Node.js**
   ```bash
   npm install
   ```

4. **Configurar entorno**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configurar base de datos**
   - Crear base de datos `task_manager_beta` en MySQL
   - Configurar credenciales en `.env`

6. **Ejecutar migraciones**
   ```bash
   php artisan migrate
   ```

7. **Compilar assets**
   ```bash
   npm run build
   ```

## 🚀 Iniciar el proyecto

### Desarrollo

1. **Iniciar servidor Laravel**
   ```bash
   php artisan serve
   ```

2. **Iniciar servidor Reverb (WebSockets)**
   ```bash
   php artisan reverb:start
   ```

3. **Compilar assets en modo desarrollo**
   ```bash
   npm run dev
   ```

### Producción

```bash
npm run build
php artisan optimize
php artisan reverb:start
```

## 📚 Documentación

Toda la documentación del proyecto se encuentra en la carpeta [`docs/`](docs/):

- [`PROJECT_STRUCTURE.md`](docs/PROJECT_STRUCTURE.md) - Estructura del proyecto y base de datos
- [`ENVIRONMENT_CHECK.md`](docs/ENVIRONMENT_CHECK.md) - Verificación del entorno
- [`REQUIREMENTS_STATUS.md`](docs/REQUIREMENTS_STATUS.md) - Estado de requisitos
- [`create_database.sql`](docs/create_database.sql) - Script SQL para crear la base de datos

## 🎯 Características (MVP)

- ✅ Autenticación (Laravel Breeze)
- ✅ Estructura de base de datos
- ✅ Modelos y relaciones
- ⏳ Proyectos y equipos
- ⏳ Tareas con Kanban
- ⏳ Roles y permisos
- ⏳ Colaboración en tiempo real
- ⏳ Notificaciones

## 📝 Licencia

Este proyecto es de código abierto y está disponible bajo la [licencia MIT](LICENSE).

