# Wallmark · Backend

API REST y panel de administración de **Wallmark**, construidos con Laravel 13 (PHP 8.4).

- **`/api/v1`** — API JSON que consume el frontend público (`../frontend/`).
- **`/admin`** — panel de administración renderizado en servidor con Blade, Tailwind CSS y Vite.

## Requisitos

- PHP >= 8.3 (probado con **8.4**) con las extensiones `curl`, `dom`, `fileinfo`, `mbstring`, `pdo_sqlite`, `xml`
- Composer 2
- Node.js >= 20.9 y npm (para compilar los assets del admin)

## Instalación

```bash
composer run setup
```

Equivale a:

```bash
composer install
copy .env.example .env                        # macOS/Linux: cp .env.example .env
php artisan key:generate
type nul > database\database.sqlite          # macOS/Linux: touch database/database.sqlite
php artisan migrate --seed
npm install
npm run build
```

> `database/*.sqlite*` está ignorado en git: en un clon nuevo hay que crear el archivo antes de migrar. Por eso `composer run setup` lo hace automáticamente.

## Comandos

| Comando | Qué hace |
|---|---|
| `composer run dev` | Servidor, cola, logs y Vite a la vez (`php artisan dev`) |
| `composer run test` | Limpia la configuración y ejecuta PHPUnit |
| `composer run setup` | Instalación completa desde cero |
| `vendor/bin/pint` | Formatea el código PHP |

## Base de datos

SQLite por defecto (`database/database.sqlite`), configurable con `DB_CONNECTION` en `.env`.

Los seeders son idempotentes (`updateOrCreate`):

- `AdminUserSeeder` — crea/actualiza la cuenta de administración desde `ADMIN_EMAIL` y `ADMIN_PASSWORD`.
- `LinkSeeder` — 6 enlaces de ejemplo con metadata estática (**sin llamadas a la red**).

## Panel de administración

Todas las rutas viven bajo `/admin`; `/` redirige ahí. Las rutas de acceso usan middleware `guest` y el resto `auth`.

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/admin/login` | Formulario de acceso |
| POST | `/admin/login` | Autentica |
| POST | `/admin/logout` | Cierra sesión |
| GET | `/admin` | Dashboard |
| GET | `/admin/links` | Lista de enlaces |
| GET | `/admin/links/{id}/edit` | Editar enlace |
| PATCH | `/admin/links/{id}` | Guarda cambios |
| DELETE | `/admin/links/{id}` | Elimina enlace |
| DELETE | `/admin/tags/{tag}` | Elimina etiqueta |

Las invitados que entran a una ruta protegida son redirigidos a `admin.login` (configurado en `bootstrap/app.php`).

**Credenciales por defecto** (configurables en `.env`):

| Variable | Valor por defecto |
|---|---|
| `ADMIN_EMAIL` | `admin@wall.test` |
| `ADMIN_PASSWORD` | `password` |

## API

Prefijo: **`/api/v1`**. Las respuestas devuelven `{ "data": ... }` mediante Eloquent API Resources, y el listado añade paginación en `meta`. Los errores de validación se devuelven como JSON en `errors` (configurado en `bootstrap/app.php`).

> **Sin autenticación por ahora**: las rutas de la API no requieren token. Laravel Sanctum está instalado y listo para cuando se agregue.

### `GET /api/v1/links`

| Parámetro | Tipo | Defecto | Descripción |
|---|---|---|---|
| `q` | string | — | Busca en `title`, `url`, `description` y `site_name` |
| `tag` | string | — | Filtra por nombre de etiqueta |
| `per_page` | int | `24` | Entre 1 y 100 |
| `page` | int | `1` | Página |

Orden: los más recientes primero, con `tags` precargados.

### `POST /api/v1/links`

```json
{ "url": "https://ejemplo.com/articulo", "tags": ["css", "frontend"] }
```

**Validación**

| Regla | Detalle |
|---|---|
| `url` | requerida, `http`/`https`, máx. 2048 caracteres, **única** → `422` con *"Esta URL ya está guardada en el muro."* |
| `tags` | opcional, array, máx. 10 elementos, cada uno máx. 40 caracteres |

**Comportamiento**

1. Descarga la página remota (timeout de conexión 5 s, total 10 s, 1 MB máximo) con `App\Services\MetadataExtractor`.
2. Lee Open Graph, Twitter Cards, `<title>`, favicon y `site_name`, resolviendo URLs relativas contra la URL original.
3. Si la descarga falla o la respuesta no es HTML, genera un **fallback** a partir de la URL, así que el guardado nunca falla por metadatos.
4. Crea las etiquetas faltantes y las normaliza a minúsculas.
5. Limitado a **30 peticiones/minuto** (`throttle`).

Devuelve `201` con el enlace y sus etiquetas.

### `PATCH /api/v1/links/{id}`

| Campo | Reglas |
|---|---|
| `title` | requerido, string, 2–200 caracteres |
| `description` | opcional, `null` para vaciar, máx. 1000 caracteres |
| `tags` | opcional, array; **reemplaza** el listado completo |

Acepta `tags` como string separado por comas (útil desde formularios HTML); lo normaliza en `UpdateLinkRequest`.

### `DELETE /api/v1/links/{id}` → `204`

### `GET /api/v1/tags?q=`

Etiquetas con su contador `links_count`, ordenadas por uso y luego alfabéticamente, máximo 20. `q` filtra por nombre (typeahead).

## Estructura

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/          LinkController, TagController (API JSON)
│   │   └── Admin/        AuthController, DashboardController, LinkController, TagController
│   ├── Requests/         StoreLinkRequest, UpdateLinkRequest (validación)
│   └── Resources/        LinkResource, TagResource (Eloquent API Resources)
├── Models/               Link, Tag, User
└── Services/
    └── MetadataExtractor.php    Extracción de metadatos OG/Twitter con fallback
database/
├── factories/            Link, Tag, User
├── migrations/
└── seeders/              AdminUserSeeder, LinkSeeder
routes/
├── api.php               /api/v1/*
└── web.php               /admin/*
tests/Feature/            AdminAuthTest, LinkApiTest
```

## Tests

```bash
composer run test
# o un archivo/uno en específico
php artisan test --compact --filter=LinkApiTest
```

| Archivo | Cubre |
|---|---|
| `LinkApiTest` | Extracción de metadata, fallback cuando la descarga falla, rechazo de URLs duplicadas o inválidas, búsqueda, filtro por etiqueta, typeahead, actualización y borrado |
| `AdminAuthTest` | Redirección de invitados, login, credenciales inválidas, logout, listado y borrado de enlaces desde el admin |
