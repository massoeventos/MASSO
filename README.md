# MASSO Eventos – Entorno de Desarrollo con Docker

Este repositorio contiene el sistema de gestión de eventos MASSO. Para facilitar el desarrollo local, el proyecto está configurado para ejecutarse usando Docker con PHP 7.4 y Apache.

---

## 🚀 Requisitos

- [Docker Desktop](https://www.docker.com/products/docker-desktop) (con WSL2 o Hyper-V activado)
- Git
- Acceso de administrador para editar el archivo `hosts` (en Windows)

---

## 📦 Clonar el proyecto

```bash
git clone git@github.com:massoeventos/MASSO.git
cd MASSO
```

---

## ⚙️ Configuración inicial

### 1. Copiar el archivo `.env`

```bash
cp .env.example .env
```

Ajusta los valores del entorno según tu configuración local. Por ejemplo, si usas MySQL en tu máquina (Windows), cambia:

```env
DB_HOST=host.docker.internal
```

---

### 2. Agregar dominios locales al archivo `hosts`

Edita el archivo `C:\Windows\System32\drivers\etc\hosts` con permisos de administrador y agrega:

```
127.0.0.1       sistema.massoeventos.test
127.0.0.1       massoeventos.test
```

---

## ▶️ Levantar el entorno

Desde la raíz del proyecto, ejecuta:

```bash
docker-compose up --build
```

Esto construirá el contenedor con PHP 7.4, instalará Apache y activará los virtual hosts.

---

## 🌐 Acceso a la aplicación

- App principal: [http://massoeventos.test](http://massoeventos.test)
- App administracion: [http://sistema.massoeventos.test](http://sistema.massoeventos.test)

---

## 🧰 Usar la consola dentro del contenedor

### Ver el nombre del contenedor

```bash
docker ps
```

### Acceder al bash del contenedor

```bash
docker exec -it nombre_del_contenedor bash
```

Ejemplo (si el contenedor se llama `masso_app_1`):

```bash
docker exec -it masso_app_1 bash
```

### Ejecutar comandos dentro del contenedor

```bash
composer install
php artisan migrate
php artisan serve
```

---

## 📂 Estructura relacionada a Docker

```
MASSO/
├── docker/
│   ├── Dockerfile
│   ├── vhost.conf
├── docker-compose.yml
├── .env
├── .env.example
├── app/
├── routes/
└── ...
```

---

## 🛠 Notas importantes

- **APP_ENV** en `.env` debe ser `local` para evitar redirecciones a HTTPS.
- **ROUTE_SYSTEM** en `.env` debe ser `http://sistema.massoeventos.test` para que las rutas funcionen correctamente.
- **ROUTE_WEB** en `.env` debe ser `http://massoeventos.test` para que las rutas funcionen correctamente.
- **Volúmenes**: el código fuente se monta como volumen desde el host para permitir desarrollo en tiempo real sin reconstrucción de imagen.
- **MySQL**: si usas una base de datos local en tu máquina Windows, usa `host.docker.internal` como `DB_HOST`.

---

## 🐞 Problemas comunes

- **No conecta a MySQL local**: asegúrate de usar `host.docker.internal` y que tu MySQL permita conexiones desde Docker.

---

## 🧑‍💻 Contribución

Si trabajas en equipo, asegúrate de mantener sincronizado el entorno Docker. Cualquier cambio en `docker-compose.yml`, `Dockerfile` o `vhost.conf` debe ser confirmado con el resto del equipo.

---

## 📬 Contacto

Proyecto desarrollado por MASSO Eventos. Si tienes dudas, comunícate con el equipo técnico.
