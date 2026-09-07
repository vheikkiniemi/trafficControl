<?php

date_default_timezone_set("Europe/Helsinki");

header("Cache-Control: no-cache");
header("Content-Type: text/event-stream");

require_once("session.php");
require_once("db.php");

$conn = OpenCon();

$counter = 0;

$data = [
    "hash"  => "",
    "time"  => "",
    "state" => ""
];


/*
|--------------------------------------------------------------------------
| Signing key
|--------------------------------------------------------------------------
|
| Prefer storing this in .env instead of directly in the PHP source.
|
*/

$sKey = getenv("SIGNING_KEY");

if (!$sKey) {
    throw new RuntimeException("SIGNING_KEY environment variable is not defined.");
}


/*
|--------------------------------------------------------------------------
| Helper function
|--------------------------------------------------------------------------
*/

function fetchLastControlId(PDO $conn): int
{
    $sql = "
        SELECT id
        FROM ohjaukset
        ORDER BY id DESC
        LIMIT 1
    ";

    $stmt = $conn->query($sql);
    $row = $stmt->fetch();

    return $row ? (int)$row["id"] : 0;
}


/*
|--------------------------------------------------------------------------
| Initial state
|--------------------------------------------------------------------------
*/

$lastIdHistory = fetchLastControlId($conn);


/*
|--------------------------------------------------------------------------
| Server-Sent Events loop
|--------------------------------------------------------------------------
*/

while (true) {

    /*
    |--------------------------------------------------------------------------
    | Check latest control record
    |--------------------------------------------------------------------------
    */

    $lastId = fetchLastControlId($conn);


    /*
    |--------------------------------------------------------------------------
    | Create timestamp and HMAC signature
    |--------------------------------------------------------------------------
    */

    $timestamp = time();

    $data["hash"] = hash_hmac(
        "sha256",
        (string)$timestamp,
        $sKey
    );

    $data["time"] = $timestamp;


    /*
    |--------------------------------------------------------------------------
    | New control command
    |--------------------------------------------------------------------------
    */

    if ($lastIdHistory < $lastId) {

        $data["state"] = 1;

        echo "data: " . json_encode(
            $data,
            JSON_UNESCAPED_UNICODE
        ) . "\n\n";

        if (ob_get_level() > 0) {
            ob_flush();
        }

        flush();

        $counter = 1;
        $lastIdHistory = $lastId;

        usleep(500000);
    }


    /*
    |--------------------------------------------------------------------------
    | Default state
    |--------------------------------------------------------------------------
    |
    | No new command has been received through the web interface.
    |
    */

    if ($counter <= 0) {

        $data["state"] = 0;

        echo "data: " . json_encode(
            $data,
            JSON_UNESCAPED_UNICODE
        ) . "\n\n";

        if (ob_get_level() > 0) {
            ob_flush();
        }

        flush();

        $counter = 20;
    }


    /*
    |--------------------------------------------------------------------------
    | Wait 500 ms
    |--------------------------------------------------------------------------
    */

    $counter--;

    usleep(500000);


    /*
    |--------------------------------------------------------------------------
    | Stop when client disconnects
    |--------------------------------------------------------------------------
    */

    if (connection_aborted()) {
        break;
    }
}

CloseCon($conn);
?>