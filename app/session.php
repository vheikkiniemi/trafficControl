<?php

/*
|--------------------------------------------------------------------------
| Session signing key
|--------------------------------------------------------------------------
|
| Store the key in an environment variable instead of source code.
|
*/

$key = getenv("SESSION_SIGNING_KEY");

if (!$key) {
    throw new RuntimeException(
        "SESSION_SIGNING_KEY environment variable is not defined."
    );
}


/*
|--------------------------------------------------------------------------
| Authenticated username
|--------------------------------------------------------------------------
*/

$omatunnus = $_SESSION["username"] ?? "0";

?>