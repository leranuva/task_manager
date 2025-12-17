# Guía de Instalación de Redis para Windows

## 📋 Opciones de Instalación

### Opción 1: Memurai (Recomendado para Windows)

Memurai es una implementación nativa de Redis para Windows, compatible al 100% con Redis.

**Pasos:**
1. Descargar Memurai desde: https://www.memurai.com/get-memurai
2. Instalar el ejecutable
3. Memurai se ejecutará como servicio de Windows automáticamente
4. Puerto por defecto: 6379

**Ventajas:**
- ✅ Instalación sencilla
- ✅ Se ejecuta como servicio
- ✅ Compatible 100% con Redis
- ✅ Soporte oficial

### Opción 2: Redis para Windows (Portable)

Versión portable de Redis para Windows (no oficial, pero funcional).

**Pasos:**
1. Descargar desde: https://github.com/microsoftarchive/redis/releases
2. Descargar `Redis-x64-3.0.504.zip` o versión más reciente
3. Extraer en una carpeta (ej: `C:\Redis`)
4. Ejecutar `redis-server.exe`

**Configuración:**
- Puerto: 6379 (por defecto)
- Archivo de configuración: `redis.windows.conf`

### Opción 3: WSL2 (Windows Subsystem for Linux)

Si tienes WSL2 instalado, puedes instalar Redis en Linux.

**Pasos:**
```bash
# En WSL2
sudo apt update
sudo apt install redis-server
sudo service redis-server start
```

### Opción 4: Docker

Si tienes Docker instalado:

```bash
docker run -d -p 6379:6379 --name redis redis:7-alpine
```

## 🔧 Verificación de Instalación

Después de instalar Redis, verifica que esté funcionando:

```bash
# Verificar que Redis está corriendo
redis-cli ping
# Debe responder: PONG

# O desde PowerShell
redis-cli --version
```

## ⚙️ Configuración en Laravel

Una vez instalado Redis, verifica que tu `.env` tenga:

```env
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

CACHE_STORE=redis
QUEUE_CONNECTION=redis
BROADCAST_CONNECTION=reverb
```

## 🧪 Probar Conexión desde Laravel

```bash
# Tinker
php artisan tinker

# En Tinker:
Cache::put('test', 'Redis funciona!', 60);
Cache::get('test');
```

## 📝 Notas

- **Puerto por defecto**: 6379
- **Host por defecto**: 127.0.0.1
- **Sin contraseña por defecto** en instalaciones locales
- **Redis debe estar ejecutándose** antes de usar Laravel

## 🐛 Solución de Problemas

### Redis no se conecta

1. Verificar que Redis esté ejecutándose:
   ```bash
   redis-cli ping
   ```

2. Verificar el puerto:
   ```bash
   netstat -an | findstr 6379
   ```

3. Verificar firewall de Windows

### Error: "Connection refused"

- Redis no está ejecutándose
- Puerto incorrecto en `.env`
- Firewall bloqueando la conexión

### Error: "phpredis extension not found"

Instalar extensión PHP Redis:
```bash
# Usando PECL (si está disponible)
pecl install redis

# O descargar DLL desde: https://pecl.php.net/package/redis
```

