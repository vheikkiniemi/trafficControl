<?php

// Open PostgreSQL database connection
function OpenCon(): PDO
{
    $dbhost = getenv("DB_HOST") ?: "db";
    $dbport = getenv("DB_PORT") ?: "5432";
    $dbname = getenv("DB_NAME") ?: "litih501_raspberry";
    $dbuser = getenv("DB_USER") ?: "litih501_raspi";
    $dbpass = getenv("DB_PASSWORD");

    if (!$dbpass) {
        throw new RuntimeException("DB_PASSWORD environment variable is not defined.");
    }

    $dsn = "pgsql:host={$dbhost};port={$dbport};dbname={$dbname}";

    return new PDO(
        $dsn,
        $dbuser,
        $dbpass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
}


// Close PostgreSQL database connection
function CloseCon(?PDO &$conn): void
{
    $conn = null;
}
?>