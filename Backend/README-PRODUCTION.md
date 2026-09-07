# Lizy Admin — production setup (MySQL)

This document is the exact sequence to get this project running on MySQL,
whether that's your own laptop for a final check, or your real server.

Everything below was actually run and verified while preparing this
package: all 25 migrations were executed against a real MySQL 8 database,
all existing data (15 services, 133 media files, 8 properties, 12 tour
packages, 28 categories, 4 locations, roles/permissions, the admin user)
was copied across and verified row-for-row, and the public API was
confirmed to serve that data — including working image URLs — over real
HTTP. The steps below reproduce exactly that.

## 1. Requirements

- PHP 8.2+ with the `pdo_mysql`, `mbstring`, `xml`, `curl`, `gd`, `zip`
  extensions (all standard on any normal hosting/PHP install)
- MySQL 8 (or MariaDB 10.6+)
- Composer (only if you ever need to reinstall `vendor/` — it's already
  included in this package, so this is optional)

## 2. Create the database

Run this once on your MySQL server (adjust the password if you want —
just make sure it matches what's in `.env`, described next):

```sql
CREATE DATABASE lizy_admin CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'lizy_admin_user'@'localhost' IDENTIFIED BY 'lizy_admin_password';
GRANT ALL PRIVILEGES ON lizy_admin.* TO 'lizy_admin_user'@'localhost';
FLUSH PRIVILEGES;
```

If your MySQL server is on a different host to the PHP app, replace
`@'localhost'` with `@'%'` (or the app server's specific IP) in both lines.

## 3. Configure `.env`

The `.env` file already shipped in this package is pre-filled to match the
SQL above, so if you used those exact values it will work immediately.
Open it and check/update just these two things for your environment:

- `APP_URL` — set this to the real domain this backend will be reached at
  (e.g. `https://admin.yourdomain.com`). This single value is what every
  uploaded image's URL is built from (`Media::getUrlAttribute()`), so
  getting it right here is what makes images work correctly once deployed
  — no other code change is needed for that.
- `DB_HOST` / `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` — only if you
  used different values than step 2.

For a from-scratch production deployment (not just this package's demo
values), copy `.env.example` to `.env` instead and fill in every value —
it's the fully annotated template with no placeholder credentials in it.

Then generate a fresh app key (skip this only if you're keeping the one
already in `.env`):

```
php artisan key:generate
```

## 4. Create the tables

```
php artisan migrate --force
```

This creates all 25 tables fresh on your MySQL database — users, roles,
permissions, locations, categories, media, products, properties,
tour_packages, services, enquiries, settings, and Laravel's own
session/cache/queue tables. Nothing here is SQLite-specific; every
migration was verified to run cleanly on real MySQL while preparing this
package.

## 5. Bring your existing data across

This package still includes the original `database/database.sqlite` file
with all your existing Services, Media, Properties, Products, Tour
Packages, Users etc. in it — nothing was deleted from it. To copy that
data into the MySQL database you just created:

```
php artisan app:migrate-sqlite-to-mysql
```

This is a purpose-built command (`app/Console/Commands/MigrateSqliteDataToMysql.php`)
that copies every table in the right dependency order, preserving IDs so
every relationship (which media belongs to which service, which category
a product belongs to, etc.) stays intact. It only reads from the SQLite
file — nothing is deleted or changed there, so it's safe to keep as a
backup and safe to re-run if needed.

You should see output ending with a table of row counts copied — compare
that against what you expect (e.g. this project had 15 services, 133
media records at the time this package was prepared).

## 6. Serve the app

For a quick local check:
```
php artisan serve
```

For real hosting, point your web server's document root at this
project's `public/` folder (standard Laravel deployment) — Apache/Nginx
configs aren't included here since they depend on your specific host.

## 7. Verify it's working

```
curl https://YOUR-DOMAIN/api/public/services
```

You should get back JSON with your services, each with a `main_image` URL
that starts with your `APP_URL`. Open one of those image URLs directly in
a browser — it should load the actual photo (this is served by
`MediaStreamController`, not the `storage:link` symlink, so it works even
on hosts where symlinks aren't allowed).

Log into the admin panel at `https://YOUR-DOMAIN/login` with your existing
admin user's credentials (copied across by step 5) and confirm you can
see/add/edit/delete a Service as before.

## 8. Point LizyNet at this backend

In the LizyNet project, open `js/data.js` and set:

```js
adminApiBase: 'https://YOUR-DOMAIN',
```

That's the only change LizyNet needs. See LizyNet's own
`README-PRODUCTION.md` for the rest of that side.

## MySQL/MariaDB "Specified key was too long" fix

If your MySQL/MariaDB server's default table engine is MyISAM rather than
InnoDB (some WAMP/XAMPP installs are configured this way), the very first
migration can fail with:

```
SQLSTATE[42000]: Syntax error or access violation: 1071 Specified key was
too long; max key length is 1000 bytes
```

This is already fixed in this package — two small, standard Laravel
compatibility settings, no migrations touched:

- `app/Providers/AppServiceProvider.php` now calls
  `Schema::defaultStringLength(191)`, capping the default length of
  unique string columns (`users.email`, every module's `slug`,
  `settings.key`, etc.) so their indexes stay well under any MySQL
  version's key-length limit.
- `config/database.php` now explicitly sets `'engine' => 'InnoDB'` for
  both the `mysql` and `mariadb` connections (previously `null`, which
  just uses whatever the server defaults to). This is what actually
  matters for composite indexes like `failed_jobs`' - InnoDB allows
  3072-byte keys, MyISAM is hard-capped at 1000 regardless of column
  length settings.

Verified against a real MySQL 8 server with its default engine forced to
MyISAM (reproducing the reported error exactly), then confirmed all 25
migrations run cleanly end-to-end from a completely empty database once
both fixes are in place, and that `php artisan app:migrate-sqlite-to-mysql`
still copies existing data correctly on top of the fixed schema.



- **Nothing in your models, controllers, or migrations needed to change**
  — none of them contained SQLite-specific code, so the same codebase
  works on both databases; only `.env`'s `DB_CONNECTION` differs.
- Added `app/Console/Commands/MigrateSqliteDataToMysql.php` — the data
  copy command described in step 5.
- Added `.env.example` — a fully annotated, credential-free template for
  a from-scratch production `.env`.
- Removed two unreferenced debris files that had ended up in
  `resources/views/` (`dashboard.blade.zip`, a stray duplicate
  `lizy-admin.js`) and a stray duplicate nested `config/cors-fix/` folder
  — none were used anywhere in the app; removing them is pure cleanup,
  no functionality changed.
- `config/cors.php` already allows the public API (`api/public/*`) and
  the admin API (`api/admin/*`) to be called from any origin — verified
  with a real cross-origin preflight request while testing. Nothing
  needed fixing here.
- Confirmed no MySQL/database credentials, API secrets, or private
  Windows/WAMP paths exist anywhere in LizyNet's frontend files — the
  frontend only ever talks to the public, read-only API endpoints.
