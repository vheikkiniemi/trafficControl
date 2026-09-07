<?php

date_default_timezone_set("Europe/Helsinki");

header("Cache-Control: no-cache");
header("Content-Type: text/event-stream");

require_once("db.php");

$conn = OpenCon();

$counter = 0;

/*
|--------------------------------------------------------------------------
| Helper function
|--------------------------------------------------------------------------
*/

function fetchLastEventId(PDO $conn): int
{
    $sql = "
        SELECT eventid
        FROM newevents
        ORDER BY id DESC
        LIMIT 1
    ";

    $stmt = $conn->query($sql);
    $row = $stmt->fetch();

    return $row ? (int)$row["eventid"] : 0;
}


/*
|--------------------------------------------------------------------------
| Initial state
|--------------------------------------------------------------------------
*/

$lastIdHistory = fetchLastEventId($conn);


/*
|--------------------------------------------------------------------------
| Load Info definitions
|--------------------------------------------------------------------------
*/

$stmt = $conn->query("
    SELECT koodi, maarittely
    FROM infot
");

$infot = [];

while ($row = $stmt->fetch()) {
    $infot[$row["koodi"]] = $row["maarittely"];
}


/*
|--------------------------------------------------------------------------
| Load change definitions
|--------------------------------------------------------------------------
*/

$stmt = $conn->query("
    SELECT koodi, maarittely
    FROM muutokset
");

$muutokset = [];

while ($row = $stmt->fetch()) {
    $muutokset[$row["koodi"]] = $row["maarittely"];
}


/*
|--------------------------------------------------------------------------
| Server-Sent Events loop
|--------------------------------------------------------------------------
*/

while (true) {

    $lastId = fetchLastEventId($conn);

    /*
    |--------------------------------------------------------------------------
    | New events available
    |--------------------------------------------------------------------------
    */

    if ($lastIdHistory < $lastId) {

        while ($lastIdHistory < $lastId) {

            $nextEventId = $lastIdHistory + 1;

            $sql = "
                SELECT eventid, event, value, time
                FROM newevents
                WHERE eventid = :eventid
            ";

            $stmt = $conn->prepare($sql);

            $stmt->execute([
                "eventid" => $nextEventId
            ]);

            $tapahtuma = $stmt->fetch();

            /*
            |--------------------------------------------------------------------------
            | If the event exists
            |--------------------------------------------------------------------------
            */

            if ($tapahtuma) {

                $eventId = (int)$tapahtuma["eventid"];
                $event   = trim($tapahtuma["event"]);
                $value   = trim($tapahtuma["value"]);
                $time    = (int)$tapahtuma["time"];

                $description = "";

                if ($event === "I") {

                    if (isset($infot[$value])) {
                        $description = $infot[$value];
                    }

                    $data = "<tr class='Info' id='" . $eventId . "'>";

                } else {

                    if (isset($muutokset[$value])) {
                        $description = $muutokset[$value];
                    }

                    $data = "<tr class='Muutos' id='" . $eventId . "'>";
                }

                $data .= "<td>" . date("d.m.Y H:i:s", $time) . "</td>";
                $data .= "<td>" . htmlspecialchars(
                    $description,
                    ENT_QUOTES | ENT_SUBSTITUTE,
                    "UTF-8"
                ) . "</td>";
                $data .= "</tr>";

                echo "data: " . $data . "\n\n";

                $lastIdHistory = $eventId;

                if (ob_get_level() > 0) {
                    ob_flush();
                }

                flush();

            } else {

                /*
                 * Prevent an infinite loop if an event ID is missing.
                 */
                $lastIdHistory = $nextEventId;
            }
        }

        $counter = 10;
    }


    /*
    |--------------------------------------------------------------------------
    | Keep connection alive
    |--------------------------------------------------------------------------
    */

    if ($counter <= 0) {

        $counter = 20;

        echo "data: " . $lastId . "\n\n";

        if (ob_get_level() > 0) {
            ob_flush();
        }

        flush();
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
    | Stop if browser disconnects
    |--------------------------------------------------------------------------
    */

    if (connection_aborted()) {
        break;
    }
}

CloseCon($conn);
?>