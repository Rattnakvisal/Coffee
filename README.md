# Coffee POS (Laravel 12)

Coffee shop management app with:
- Admin dashboard (users, products, categories, inventory, reports, settings)
- Cashier workflow (checkout, attendance, history)
- Dockerized production-style runtime (Nginx + PHP-FPM + MySQL + Redis + Mailpit)

## Stack

- PHP 8.4 (FPM)
- Laravel 12
- MySQL 8
- Redis 7
- Nginx
- Vite 7 + TailwindCSS 4

## Quick Start (Docker)

1. Clone project and enter directory.
2. Copy env file:
   ```bash
   cp .env.example .env
   ```
3. Start containers:
   ```bash
   docker compose up -d --build
   ```
4. Run migrations:
   ```bash
   docker compose exec app php artisan migrate --force
   ```

## Access URLs

- App: `http://localhost:8000`
- Admin login: `http://localhost:8000/login/admin`
- Mailpit UI: `http://localhost:8025`

## Default Accounts

Created by migration `2026_03_20_040000_ensure_default_users_exist.php`.

- Admin
  - Email: `admin@coffee.test`
  - Password: `admin12345`
- Cashier
  - Email: `cashier@coffee.test`
  - Password: `cashier12345`

## Useful Docker Commands

- Start:
  ```bash
  docker compose up -d
  ```
- Rebuild app service:
  ```bash
  docker compose up -d --build app
  ```
- Stop:
  ```bash
  docker compose down
  ```
- Logs:
  ```bash
  docker compose logs -f app
  ```
- Run artisan commands:
  ```bash
  docker compose exec app php artisan <command>
  ```

## Environment Notes

- App HTTP port is configurable with `APP_PORT` (default `8000`) in `docker-compose.yml`:
  - `${APP_PORT:-8000}:80`
- Compose uses these MySQL env keys (with defaults if missing):
  - `MYSQL_DATABASE`
  - `MYSQL_USER`
  - `MYSQL_PASSWORD`
  - `MYSQL_ROOT_PASSWORD`

## Frontend Asset Notes

- Production container serves built assets from `public/build`.
- Vite hot mode is disabled in Docker image (`public/hot` is removed during build).
- If you change frontend files, rebuild app image:
  ```bash
  docker compose up -d --build app
  ```

## Troubleshooting

- 404 on `/login/admin`:
  - Run migrations:
    ```bash
    docker compose exec app php artisan migrate --force
    ```
- CSS/JS not loading and browser shows `:5173` errors:
  - Rebuild app image:
    ```bash
    docker compose up -d --build app
    ```
  - Hard refresh browser (`Ctrl+F5`).

## Local (Non-Docker) Dev

If running without Docker:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run dev
php artisan serve
```
