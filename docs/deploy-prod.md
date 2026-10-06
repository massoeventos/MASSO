# Despliegue a producción (EC2 + Docker)

Cómo subir cambios de `dev` a producción. Producción **no** es Laravel Cloud (eso es solo un sandbox) — es una instancia EC2 con un único contenedor Docker (`masso_app`), `git pull` directo sobre el checkout en el servidor.

## Cosas del entorno que hay que tener presentes

- **`public/` es un bind-mount real del host** (`.:/var/www/html:cached` en `docker-compose.yml`). Lo que esté en `/home/ubuntu/massoeventos` en el servidor es exactamente lo que sirve Apache dentro del contenedor — no hace falta reconstruir la imagen para que un cambio de archivos se vea.
- **`vendor/` es un volumen aparte**, no bind-mount — por eso `composer install` tiene que correr *dentro* del contenedor (`docker exec`), nunca en el host.
- **No hay Node/npm instalado**, ni en el host ni en el contenedor, **a propósito** (el host se mantiene mínimo). El build de assets se hace con un contenedor Docker efímero (`node:20`), nunca instalando nada permanente.
- **OPcache está activo** dentro del contenedor, con los valores por defecto de PHP (verificado 2026-10-06: `opcache.validate_timestamps=On`, `opcache.revalidate_freq=2`). PHP revisa cada 2 s si un archivo cambió y lo recompila solo, así que **un `git pull` de código no requiere reiniciar el contenedor**. Para volver a verificarlo: `docker exec masso_app php -i | grep -E "opcache.enable |validate_timestamps|revalidate_freq"`. Si algún día `validate_timestamps` aparece en `Off`, el reinicio pasa a ser obligatorio en cada deploy.
- `docker-compose.yml` y `docker/vhost.conf` tienen cambios locales en el servidor **sin commitear** (puerto 80 para el ALB de AWS, dominio real, logging) — un `git pull` normal no los toca porque los commits de `dev` no tocan esos archivos. Si algún día sí los tocan, revisar con cuidado antes de pisarlos.
- `origin` en el repo del servidor sí apunta a GitHub (`git@github.com:massoeventos/MASSO.git`), así que `git fetch`/`git pull` funcionan normales ahí.
- **El scheduler de Laravel (`masso:send`, crea las inscripciones en `events_enroll` a partir de pagos confirmados) depende de un cron a nivel del HOST EC2**, no de nada dentro del contenedor — confirmado que ya existe: `crontab -l` del usuario que despliega muestra `* * * * * docker exec masso_app php artisan schedule:run >> /dev/null 2>&1`. Si alguna vez se migra a un servidor nuevo desde cero, este crontab hay que volver a crearlo a mano (no viaja con el código ni con la imagen) — agregarlo al smoke test del paso 10.

## Pasos

**0. Backup de la BD**, antes de avisar que vas a desplegar.

**1. Modo mantenimiento** (bloquea compras/escrituras mientras se actualiza):
```bash
docker exec masso_app php artisan down
```

**2. Fusionar `dev` → `master`** en GitHub, y traer el código al servidor:
```bash
cd /home/ubuntu/massoeventos
git pull origin master
git status   # confirmar que docker-compose.yml y docker/vhost.conf siguen con los cambios locales intactos
```

**3. Build de assets**, con un contenedor Node temporal y descartable (no se instala nada permanente):
```bash
docker run --rm -v "$(pwd)":/app -w /app node:20 sh -c "npm install && npm run production"
ls public/css public/js            # confirmar que generó archivos con hash nuevo
cat public/mix-manifest.json       # debe listar los 6 bundles (public/panel/frontend x css/js)
```
Si el servidor exige que los archivos queden con tu usuario y no `root`, agregar `--user "$(id -u):$(id -g)"` al comando.

**4. Composer, dentro del contenedor:**
```bash
docker exec masso_app composer install --no-dev --optimize-autoloader
```

**5. Migraciones:**
```bash
docker exec masso_app php artisan migrate --force
```

**6. Backfills de datos — solo si ese despliegue en particular agrega alguno nuevo.** No es un paso fijo de todos los despliegues; cada feature que lo necesite trae su propio comando `masso:backfill-...` (ver `app/Console/Commands/`). Si corres varios, el orden importa — ver el comentario en `RestoreStagingFromDump.php` para el orden correcto entre ellos.

**7. Variables de entorno nuevas** — si el despliegue agrega alguna, editarlas a mano en el `.env` real del servidor (nunca viajan por git, hay que agregarlas ahí cada vez).

**8. Limpiar caché:**
```bash
docker exec masso_app php artisan config:clear
docker exec masso_app php artisan route:clear
docker exec masso_app php artisan view:clear
```

**Reinicio o recreación del contenedor — solo si el deploy lo requiere:**

| Cambio en el deploy | Qué hacer |
| --- | --- |
| Código PHP, vistas, migraciones, assets | Nada (OPcache revalida solo, ver nota arriba) |
| `.env` | Nada, mientras no se use `config:cache` (Laravel lo lee en cada request) |
| `php.ini` / extensiones PHP | `docker restart masso_app` |
| `docker-compose.yml` | `docker compose up -d` (recrea el contenedor) |
| `docker/Dockerfile` o `docker/vhost.conf` | `docker compose up -d --build` |

**9. Salir de mantenimiento:**
```bash
docker exec masso_app php artisan up
```

**10. Smoke test** antes de cerrar: que el panel admin cargue con estilos (si algo del build falló, esto se rompe primero — "jQuery is not defined" es la señal clásica), probar la pantalla nueva del feature que se subió, y confirmar `crontab -l` (ver nota arriba sobre el scheduler) si es un servidor nuevo.

## Diagnóstico rápido si algo sale mal

```bash
docker ps                                          # el contenedor sigue corriendo?
docker logs masso_app --tail 100                   # errores recientes de Apache/PHP
docker exec masso_app tail -100 storage/logs/laravel.log
```

Para volver atrás: `git log --oneline -5` en el servidor, `git checkout <commit-anterior>`, reconstruir assets del paso 3 con ese commit (el código PHP lo toma OPcache solo). La BD no se revierte sola — por eso el backup del paso 0 es obligatorio, no opcional.
