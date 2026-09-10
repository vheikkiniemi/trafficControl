# Legacy PHP Modernization with Docker

A minimal container environment for modernizing an older PHP + MySQL project.
This starter uses PostgreSQL as the database and PHP PDO for database access.

## Services

- `web` — PHP 8.4 + Apache
- `db` — PostgreSQL 17

## Project structure

```text
trafficControl/
├── app/                  # Put your PHP project here
│   ├── db.php
│   ├── index.php
│   └── ...
├── apache/
│   └── 000-default.conf
├── db/
│   └── init/             # PostgreSQL initialization scripts
│       ├── 001_infot.sql
│       └── ...
├── .env.example
├── .gitignore
├── docker-compose.yml
├── Dockerfile
└── README.md
```

## Start the project

1. Create the environment file:

    ```bash
    cp .env.example .env
    ```

2. Change the password in `.env`.

3. Build and start:

    ```bash
    docker compose up -d --build
    ```

4. Open PHP application: http://localhost:8080

## Adminer connection

Use:

```text
System: PostgreSQL
Server: db
Username: value of DB_USER
Password: value of DB_PASSWORD
Database: value of DB_NAME
```

## Useful commands

```bash
docker compose ps
docker compose logs -f web
docker compose logs -f db
docker compose exec web bash
docker compose exec db psql -U legacy_user -d legacy_app
```

## Important database initialization behavior

Files under `db/init/` are executed only when PostgreSQL creates a new empty data directory. If you change the initialization SQL later, either apply the SQL manually or recreate the development database:

```bash
docker compose down -v db 
docker compose up -d --build db
```

**Warning:** `down -v` deletes the PostgreSQL Docker volume and therefore all database data.