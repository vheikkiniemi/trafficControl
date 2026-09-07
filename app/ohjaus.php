<?php

date_default_timezone_set("Europe/Helsinki");

session_start();

require_once("session.php");
require_once("db.php");

$conn = OpenCon();


/*
|--------------------------------------------------------------------------
| Validate control request
|--------------------------------------------------------------------------
|
| Ensure the request originates from the authenticated control interface.
|
*/

if (
    isset($_POST["hash"], $_POST["oKey"])
    && $omatunnus !== "0"
) {

    $expectedHash = hash_hmac(
        "sha256",
        (string)$_POST["oKey"],
        $key
    );

    if (hash_equals($expectedHash, $_POST["hash"])) {

        /*
        |--------------------------------------------------------------------------
        | Store control event
        |--------------------------------------------------------------------------
        */

        $sql = "
            INSERT INTO ohjaukset
                (state, username, ip)
            VALUES
                (:state, :username, :ip)
        ";

        $stmt = $conn->prepare($sql);

        $stmt->execute([
            "state"    => true,
            "username" => $omatunnus,
            "ip"       => $_SERVER["REMOTE_ADDR"] ?? null
        ]);
    }
}


CloseCon($conn);
?>