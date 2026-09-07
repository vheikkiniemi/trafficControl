<?php

date_default_timezone_set("Europe/Helsinki");

require_once("db.php");

$conn = OpenCon();

/*
|--------------------------------------------------------------------------
| Base query
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        t.id AS id,
        t.time AS aika,
        m.maarittely AS mmaarittely,
        i.maarittely AS imaarittely,
        RIGHT(t.value, 1) AS value
    FROM liikennevalot_nodes_events t
    LEFT JOIN muutokset m
        ON t.value = m.koodi
        AND t.event = 'C'
    LEFT JOIN infot i
        ON t.value = i.koodi
        AND t.event = 'I'
";


/*
|--------------------------------------------------------------------------
| Fetch one row by ID
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["rowId"])) {

    $sql .= "
        WHERE t.id = :row_id
    ";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        "row_id" => (int)$_GET["rowId"]
    ]);

} else {

    /*
    |--------------------------------------------------------------------------
    | Fetch latest 300 rows
    |--------------------------------------------------------------------------
    */

    $sql .= "
        ORDER BY t.id DESC
        LIMIT 300
    ";

    $stmt = $conn->query($sql);
}


/*
|--------------------------------------------------------------------------
| Generate HTML rows
|--------------------------------------------------------------------------
*/

while ($tietue = $stmt->fetch()) {

    $id = (int)$tietue["id"];
    $time = (int)$tietue["aika"];

    $mMaarittely = $tietue["mmaarittely"];
    $iMaarittely = $tietue["imaarittely"];

    $value = trim((string)$tietue["value"]);


    /*
    |--------------------------------------------------------------------------
    | Info event
    |--------------------------------------------------------------------------
    */

    if (empty($mMaarittely)) {

        printf(
            "<tr class='Info' id='%d'>",
            $id
        );

        printf(
            "<td>%s</td>",
            date("d.m.Y H:i:s", $time)
        );

        printf(
            "<td>%s</td></tr>",
            htmlspecialchars(
                (string)$iMaarittely,
                ENT_QUOTES | ENT_SUBSTITUTE,
                "UTF-8"
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Change event ending with 0
    |--------------------------------------------------------------------------
    */

    if (empty($iMaarittely) && $value === "0") {

        printf(
            "<tr class='Muutos' id='%d'>",
            $id
        );

        printf(
            "<td><span>-</span> %s</td>",
            date("d.m.Y H:i:s", $time)
        );

        printf(
            "<td>%s</td></tr>",
            htmlspecialchars(
                (string)$mMaarittely,
                ENT_QUOTES | ENT_SUBSTITUTE,
                "UTF-8"
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Change event ending with 1
    |--------------------------------------------------------------------------
    */

    if (empty($iMaarittely) && $value === "1") {

        printf(
            "<tr class='Muutos MuutosS' id='%d'>",
            $id
        );

        printf(
            "<td>%s</td>",
            date("d.m.Y H:i:s", $time)
        );

        printf(
            "<td>%s</td></tr>",
            htmlspecialchars(
                (string)$mMaarittely,
                ENT_QUOTES | ENT_SUBSTITUTE,
                "UTF-8"
            )
        );
    }
}


/*
|--------------------------------------------------------------------------
| Close connection
|--------------------------------------------------------------------------
*/

CloseCon($conn);
?>