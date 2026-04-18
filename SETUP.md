# Loyalty System Setup (Symfony 6 + MySQL + Docker)

## 1) Start infrastructure

```bash
docker compose up -d --build
```

Services:
- Symfony PHP-FPM: `http://localhost:8080` via Nginx
- phpMyAdmin: `http://localhost:8081`
- MySQL (host): `localhost:3308`

phpMyAdmin login notes:
- Server/Host in phpMyAdmin: `mysql` (container network), not `localhost`
- Port in phpMyAdmin: `3306` (or leave empty if prefilled)
- Username: `root`
- Password: `root`

## 2) Create Symfony project inside PHP container

If the repository is empty, run:

```bash
docker compose run --rm php composer create-project symfony/skeleton:^6.4 .
```

If the repository is not empty (your current case), run this instead:

```bash
docker compose run --rm php sh -lc 'composer create-project symfony/skeleton:^6.4 /tmp/symfony && cp -an /tmp/symfony/. /var/www/html/'
```

This avoids the "directory is not empty" error and keeps existing Docker files.

Then install common backend packages for API + Doctrine:

```bash
docker compose run --rm php composer require symfony/runtime symfony/console

docker compose run --rm php composer require symfony/orm-pack doctrine/doctrine-migrations-bundle
```

## 3) Ensure .env contains MySQL connection

Use this DATABASE_URL (already configured in `.env`):

```dotenv
DATABASE_URL="mysql://loyalty_user:loyalty_pass@mysql:3306/loyalty_db?serverVersion=8.0&charset=utf8mb4"
```

## 4) Verify Doctrine connection

Run:

```bash
docker compose exec php php bin/console doctrine:query:sql "SELECT 1"
```

If this returns one row, Doctrine is connected.

## 5) Run initial migration workflow

```bash
docker compose exec php php bin/console make:migration
docker compose exec php php bin/console doctrine:migrations:migrate -n
```

## Notes
- MySQL credentials are defined in `docker-compose.yml`.
- Port `3308` is host-only mapping. Containers still connect to MySQL on `mysql:3306`.
- Nginx points to Symfony public directory (`/public`).
- If permissions issues appear, run composer as your host UID/GID or add a custom user in Dockerfile.
