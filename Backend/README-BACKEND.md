# Lizy Admin — Backend-Only Build

This is the **Lizy Admin Laravel backend, extracted as a standalone API
project** to be served independently and consumed by the separate React
admin frontend (`lizy-admin-react`). It was created as a fresh copy of the
original full-stack Laravel project — the original project has not been
modified and remains your backup.

## What was removed, and why

Only the old **Blade admin frontend** was removed — nothing that the API
depends on:

| Removed | Reason |
|---|---|
| `resources/views/*.blade.php` (all of it — dashboard, products, properties, packages, services, categories, locations, media, enquiries, settings, login, module-disabled, lizygo, lizymart, lizyreality, welcome) | These rendered the old Blade admin UI. That UI now lives entirely in the separate React project. |
| `public/js/lizy-admin.js` | The ~2,000-line JS engine that rendered the Blade admin's tables/forms. No longer needed — the React app has its own frontend code. |
| `public/lizyrealty-assets/` | Static CSS/JS bundle (Bootstrap, jQuery, etc.) used only by `resources/views/lizyreality/*.blade.php`, which is removed. Confirmed via grep that nothing else referenced this folder. |
| `app/Http/Controllers/LizyRealityController.php` | Confirmed via project-wide grep that **no route anywhere referenced this controller** — it was already dead code in the original project, unrelated to this cleanup. |
| `routes/web.php` contents | Rewritten to keep only what the backend still needs (see below) — every page route (`/dashboard`, `/products`, `/login`, etc.) that returned one of the removed Blade views is gone. |

## What was kept completely intact

Everything the React frontend calls through the API, unchanged:

- **`routes/api.php`** — byte-for-byte identical to the original. Every
  `/api/admin/*` and `/api/public/*` endpoint the React app uses is still
  here, with the same request/response shapes.
- **All Models** (`app/Models/*`), **all API Controllers**
  (`app/Http/Controllers/Api/Admin/*`, `Api/Public/*`), **all API
  Resources** (`app/Http/Resources/*`), **Enums**, **Console Commands**
- **Auth & permissions**: Sanctum token auth, `role`/`permission`
  middleware, `EnsureUserHasRole`/`EnsureUserHasPermission`
- **Media handling**: `MediaStreamController` and its `/files/{path}`
  route are kept in `routes/web.php` — every media URL the API returns
  (`main_image`, `gallery`, `banner_image`, category banners, etc.) still
  resolves correctly.
- **Database**: all migrations, seeders, factories, and the existing
  `database/database.sqlite` (with its data) are untouched. `.env` still
  points at MySQL exactly as before.
- **Uploaded media files**: `storage/app/public/media/` is included as-is
  — this is real uploaded content the database records reference, not
  build output, so it ships with the project (this is why the zip is
  large — see below).
- **`config/portal.php`** (per-module CTAs, pagination limits) and every
  other config file — unchanged.
- **CORS** (`config/cors.php`) — unchanged; already allows `api/*` from
  any origin, so the React app (on its own dev server / domain) can call
  it without further changes.

## New minimal `routes/web.php`

```php
GET  /            → JSON health check ({"status":"ok", ...}) instead of the old dashboard view
GET  /files/{path} → MediaStreamController (unchanged) — required by the API's media URLs
```

That's the entire web surface now. All real functionality is under
`/api/admin/*` (Sanctum-authenticated) and `/api/public/*` (open,
read-only), exactly as before.

## Running this independently

Same steps as `README-PRODUCTION.md` in this project (still accurate —
DB setup, `.env`, migrations, data copy). One difference: there is no
`/login` page anymore — logging in is done by the React frontend calling
`POST /api/admin/login`, not by visiting a URL on this backend.

```bash
composer install          # optional — vendor/ is already included
php artisan migrate --force
php artisan serve         # or point your web server's document root at public/
```

Then point the React app's `VITE_API_BASE_URL` at wherever this backend
is served.

## A note on zip size

To keep this deliverable practical to download, this zip **excludes**
two large, regenerable/redundant folders:

- **`vendor/`** (~92MB) — restore with `composer install` (same pattern
  as `node_modules` for the React project: not source code, just cached
  dependencies pinned by `composer.lock`).
- **`storage/app/public/media/`** (~408MB, 157 uploaded files) — this is
  your real uploaded media, already fully intact in your original project
  backup (the zip you uploaded to me), so it isn't duplicated here. Copy
  that folder back from your original project before running this
  backend against real data — every DB record's media path expects files
  at that exact location.

Both folders exist here as empty placeholders (with their `.gitignore`
files) so the app doesn't error on missing directories — just restore
their contents from the sources above.

If you'd rather have a single self-contained zip with both included
(~450MB total), let me know and I'll package that version instead.
