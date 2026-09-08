<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';


/*
|--------------------------------------------------------------------------
| Validate access
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['id'], $_SESSION['index'], $_GET['skey'])
    || $_SESSION['index'] !== true
) {
    exit;
}

$expectedSessionKey = hash_hmac(
    'sha256',
    (string)$_SESSION['id'],
    $key
);

if (!hash_equals($expectedSessionKey, (string)$_GET['skey'])) {
    exit;
}


/*
|--------------------------------------------------------------------------
| Registration state
|--------------------------------------------------------------------------
*/

$message = null;
$messageClass = null;

$conn = null;


/*
|--------------------------------------------------------------------------
| Handle registration
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['register'])
) {

    $username = trim((string)($_POST['username'] ?? ''));
    $passwordPlain = (string)($_POST['password'] ?? '');
    $userType = (string)($_POST['usertypelist'] ?? '');

    if (
        $username !== ''
        && $passwordPlain !== ''
        && in_array($userType, ['user', 'node'], true)
    ) {

        try {

            $conn = OpenCon();

            $register = false;


            /*
            |--------------------------------------------------------------------------
            | Check duplicate username
            |--------------------------------------------------------------------------
            */

            if ($userType === 'user') {

                $stmt = $conn->prepare(
                    'SELECT 1
                     FROM users
                     WHERE username = :username
                     LIMIT 1'
                );

                $stmt->execute([
                    'username' => $username
                ]);

                $register = $stmt->fetch() === false;
            }


            /*
            |--------------------------------------------------------------------------
            | Check duplicate node ID
            |--------------------------------------------------------------------------
            */

            if ($userType === 'node') {

                $stmt = $conn->prepare(
                    'SELECT 1
                     FROM nodes
                     WHERE nodeid = :nodeid
                     LIMIT 1'
                );

                $stmt->execute([
                    'nodeid' => $username
                ]);

                $register = $stmt->fetch() === false;
            }


            /*
            |--------------------------------------------------------------------------
            | Register user
            |--------------------------------------------------------------------------
            */

            if ($register && $userType === 'user') {

                $passwordHash = password_hash(
                    $passwordPlain,
                    PASSWORD_DEFAULT
                );

                $stmt = $conn->prepare(
                    'INSERT INTO users
                        (username, password)
                     VALUES
                        (:username, :password)'
                );

                $register = $stmt->execute([
                    'username' => $username,
                    'password' => $passwordHash
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Register node
            |--------------------------------------------------------------------------
            */

            if ($register && $userType === 'node') {

                $passwordHash = password_hash(
                    $passwordPlain,
                    PASSWORD_DEFAULT
                );

                $nodeKey = bin2hex(random_bytes(32));

                $stmt = $conn->prepare(
                    'INSERT INTO nodes
                        (nodeid, nodekey, password)
                     VALUES
                        (:nodeid, :nodekey, :password)'
                );

                $register = $stmt->execute([
                    'nodeid'   => $username,
                    'nodekey'  => $nodeKey,
                    'password' => $passwordHash
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Result
            |--------------------------------------------------------------------------
            */

            if ($register) {

                CloseCon($conn);

                header(
                    'Location: login.php?skey='
                    . urlencode((string)$_GET['skey'])
                );

                exit;
            }

            $message = 'Rekisteröityminen epäonnistui!';
            $messageClass = 'failedregister';

        } catch (PDOException $e) {

            $message = 'Rekisteröityminen epäonnistui!';
            $messageClass = 'failedregister';

        } finally {

            if ($conn instanceof PDO) {
                CloseCon($conn);
            }
        }

    } else {

        $message = 'Täytä kaikki kentät.';
        $messageClass = 'failedregister';
    }
}


/*
|--------------------------------------------------------------------------
| Output helper
|--------------------------------------------------------------------------
*/

function e(string $value): string
{
    return htmlspecialchars(
        $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

?>
<!doctype html>
<html lang="fi">

<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Rekisteröidy</title>

    <link
        rel="stylesheet"
        href="register.css"
    >

    <link
        rel="icon"
        href="data:image/png;base64,iVBORw0KGgo="
        type="image/png"
    >
</head>

<body>

<div id="registercenter">

    <div id="header">
        Rekisteröityminen
    </div>

    <?php if ($message !== null): ?>

        <div id="<?= e($messageClass ?? '') ?>">
            <?= e($message) ?>
        </div>

    <?php endif; ?>


    <div class="registerform">

        <form
            action="<?= e($_SERVER['PHP_SELF']) ?>?skey=<?= urlencode((string)$_GET['skey']) ?>"
            method="post"
        >

            <p>
                <label for="username"></label>

                <input
                    type="text"
                    name="username"
                    placeholder="Tunnus"
                    id="username"
                    required
                    size="50"
                    autocomplete="username"
                >
            </p>

            <p>
                <label for="password"></label>

                <input
                    type="password"
                    name="password"
                    placeholder="Salasana"
                    id="password"
                    required
                    autocomplete="new-password"
                >
            </p>

            <p>
                <label for="usertype"></label>

                <select
                    id="usertype"
                    name="usertypelist"
                    required
                >
                    <option
                        disabled
                        value=""
                        selected
                        hidden
                    >
                        Käyttäjän tyyppi
                    </option>

                    <option value="user">
                        Käyttäjä
                    </option>

                    <option value="node">
                        Node
                    </option>
                </select>
            </p>

            <p>
                <input
                    type="submit"
                    name="register"
                    id="registerbutton"
                    value="Rekisteröidy"
                >
            </p>

        </form>

    </div>

</div>

</body>
</html>