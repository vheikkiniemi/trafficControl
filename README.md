# Legacy PHP Modernization with Docker

A minimal container environment for modernizing an older PHP + MySQL project.
This starter uses PostgreSQL as the database and PHP PDO for database access.

## Services

- `web` — PHP 8.4 + Apache
- `db` — PostgreSQL 17
- `adminer` — browser-based database administration

## Project structure

```text
trafficControl/
├── app/                  # Put your PHP project here
│   ├── db.php
│   └── index.php
├── apache/
│   └── 000-default.conf
├── db/
│   └── init/             # PostgreSQL initialization scripts
│       ├── 001_schema.sql
│       └── 002_data.sql
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

4. Open:

- PHP application: http://localhost:8080
- Adminer: http://localhost:8081

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

Files under `db/init/` are executed only when PostgreSQL creates a new empty data directory.
If you change the initialization SQL later, either apply the SQL manually or recreate the development database:

```bash
docker compose down -v db 
docker compose up -d --build db
```

**Warning:** `down -v` deletes the PostgreSQL Docker volume and therefore all database data.

## Migrating the old PHP application

A practical migration order is:

1. Copy the old PHP files into `app/`.
2. Get the application running in the PHP container before changing too much code.
3. Identify old MySQL functions such as `mysql_query()` or `mysqli_*`.
4. Replace database access with PDO.
5. Convert MySQL/MariaDB SQL syntax to PostgreSQL.
6. Move credentials and configuration to environment variables.
7. Test one page or feature at a time.

### Typical old MySQL code

```php
$conn = mysqli_connect('localhost', 'user', 'password', 'database');
$result = mysqli_query($conn, 'SELECT * FROM Infot');
```

### Recommended PDO/PostgreSQL style

```php
require __DIR__ . '/db.php';

$stmt = $pdo->query('SELECT * FROM infot');
$rows = $stmt->fetchAll();
```

## MySQL-specific SQL to watch for

Common changes include:

- Backticks: `` `table` `` → usually `table`
- `AUTO_INCREMENT` → `GENERATED ... AS IDENTITY`
- `TINYINT(1)` → `BOOLEAN`
- `DATETIME` → often `TIMESTAMP`
- `ENGINE=...` → remove
- `CHARSET=...` → remove
- `IFNULL()` → `COALESCE()`
- `NOW()` works in PostgreSQL
- `LIMIT offset,count` → `LIMIT count OFFSET offset`
- MySQL `ENUM` requires redesign or a PostgreSQL enum/check constraint

## Recommended modernization principle

First containerize the old application with as few code changes as possible. After that, modernize incrementally. This makes it much easier to distinguish container problems from application migration problems.
