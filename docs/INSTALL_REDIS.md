# 🚀 Instalación Rápida de Redis para Windows

## ⚡ Opción Más Rápida: Memurai (Recomendado)

**Memurai** es la solución más sencilla y recomendada para Windows.

### Pasos:

1. **Descargar Memurai**
   - Visita: https://www.memurai.com/get-memurai
   - Descarga el instalador (gratis para desarrollo)

2. **Instalar**
   - Ejecuta el instalador
   - Sigue el asistente de instalación
   - Memurai se instalará como servicio de Windows

3. **Verificar**
   ```bash
   redis-cli ping
   # Debe responder: PONG
   ```

4. **Listo** ✅
   - Redis estará disponible en `127.0.0.1:6379`
   - No requiere configuración adicional

---

## 🔧 Opción Alternativa: Redis Portable

Si prefieres una versión portable sin instalador:

### Pasos:

1. **Descargar Redis Portable**
   - Visita: https://github.com/microsoftarchive/redis/releases
   - Descarga: `Redis-x64-3.0.504.zip` (o versión más reciente)

2. **Extraer**
   - Extrae en: `C:\Redis` (o la carpeta que prefieras)

3. **Ejecutar Redis**
   ```bash
   cd C:\Redis
   redis-server.exe
   ```

4. **Agregar al PATH (Opcional)**
   - Agrega `C:\Redis` al PATH del sistema
   - Esto permitirá usar `redis-cli` desde cualquier lugar

5. **Ejecutar al inicio (Opcional)**
   - Crea un acceso directo en la carpeta de inicio
   - O configura como servicio usando NSSM (Non-Sucking Service Manager)

---

## 🐳 Opción con Docker (Si tienes Docker)

```bash
docker run -d -p 6379:6379 --name redis redis:7-alpine
```

Para iniciar después:
```bash
docker start redis
```

---

## 🧪 Verificar Instalación

Después de instalar, verifica que funcione:

```bash
# Opción 1: Desde línea de comandos
redis-cli ping
# Debe responder: PONG

# Opción 2: Verificar versión
redis-cli --version

# Opción 3: Desde Laravel Tinker
php artisan tinker
>>> Cache::put('test', 'Redis funciona!', 60);
>>> Cache::get('test');
```

---

## ⚙️ Configuración en Laravel

Tu archivo `.env` ya está configurado para Redis:

```env
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

CACHE_STORE=redis
QUEUE_CONNECTION=redis
BROADCAST_CONNECTION=reverb
```

**No necesitas cambiar nada** si instalas Redis en el puerto 6379 por defecto.

---

## 🔍 Verificar Extensión PHP Redis

Laravel necesita la extensión PHP `phpredis` o `predis`. 

**Verificar si está instalada:**
```bash
php -m | findstr redis
```

**Si no está instalada:**

### Opción A: Usar Predis (Recomendado - No requiere extensión)

```bash
composer require predis/predis
```

Luego en `.env`:
```env
REDIS_CLIENT=predis
```

### Opción B: Instalar extensión phpredis

1. Descargar DLL desde: https://pecl.php.net/package/redis
2. Colocar en carpeta `ext` de PHP
3. Agregar a `php.ini`: `extension=redis`

---

## ✅ Checklist de Instalación

- [ ] Redis instalado y ejecutándose
- [ ] `redis-cli ping` responde `PONG`
- [ ] Extensión PHP Redis instalada (o usar Predis)
- [ ] `.env` configurado correctamente
- [ ] Laravel puede conectarse a Redis

---

## 🐛 Solución de Problemas

### "Connection refused"
- Redis no está ejecutándose
- Inicia Redis: `redis-server.exe` o inicia el servicio Memurai

### "phpredis extension not found"
- Instala Predis: `composer require predis/predis`
- O instala la extensión phpredis

### "Port 6379 already in use"
- Otro proceso está usando el puerto
- Cambia el puerto en Redis y en `.env`

---

## 📚 Recursos

- **Memurai**: https://www.memurai.com
- **Redis para Windows**: https://github.com/microsoftarchive/redis/releases
- **Predis (Cliente PHP)**: https://github.com/predis/predis
- **Documentación Redis**: https://redis.io/docs

