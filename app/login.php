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
| State
|--------------------------------------------------------------------------
*/

$message = null;
$messageClass = null;


/*
|--------------------------------------------------------------------------
| Handle POST requests
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	/*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */

	if (isset($_POST['login'])) {

		$username = trim((string)($_POST['username'] ?? ''));
		$password = (string)($_POST['password'] ?? '');

		if ($username !== '' && $password !== '') {

			$conn = null;

			try {

				$conn = OpenCon();

				$stmt = $conn->prepare(
					'SELECT password
                     FROM users
                     WHERE username = :username
                     LIMIT 1'
				);

				$stmt->execute([
					'username' => $username
				]);

				$user = $stmt->fetch();

				if (
					$user
					&& password_verify(
						$password,
						(string)$user['password']
					)
				) {

					/*
                    |--------------------------------------------------------------------------
                    | Successful login
                    |--------------------------------------------------------------------------
                    */

					session_regenerate_id(true);

					$_SESSION['username'] = $username;

					CloseCon($conn);

					header('Location: index.php');
					exit;
				}

				$message = 'Kirjautuminen epäonnistui!';
				$messageClass = 'failedlogin';
			} catch (PDOException $e) {

				$message = 'Kirjautuminen epäonnistui!';
				$messageClass = 'failedlogin';
			} finally {

				if ($conn instanceof PDO) {
					CloseCon($conn);
				}
			}
		} else {

			$message = 'Anna käyttäjätunnus ja salasana.';
			$messageClass = 'failedlogin';
		}
	}


	/*
    |--------------------------------------------------------------------------
    | Registration
    |--------------------------------------------------------------------------
    */

	if (isset($_POST['register'])) {

		header(
			'Location: register.php?skey='
				. urlencode((string)$_GET['skey'])
		);

		exit;
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
		content="width=device-width, initial-scale=1">

	<title>Kirjaudu</title>

	<link
		rel="stylesheet"
		href="login.css">

	<link
		rel="icon"
		href="data:image/png;base64,iVBORw0KGgo="
		type="image/png">
</head>

<body>

	<div id="logincenter">

		<div id="header">
			Kirjautuminen
		</div>

		<?php if ($message !== null): ?>

			<div id="<?= e($messageClass ?? '') ?>">
				<?= e($message) ?>
			</div>

		<?php endif; ?>


		<div class="loginform">

			<form
				action="<?= e($_SERVER['PHP_SELF']) ?>?skey=<?= urlencode((string)$_GET['skey']) ?>"
				method="post">

				<p>
					<label for="username"></label>

					<input
						type="text"
						name="username"
						placeholder="Tunnus"
						id="username"
						required
						autocomplete="username">
				</p>

				<p>
					<label for="password"></label>

					<input
						type="password"
						name="password"
						placeholder="Salasana"
						id="password"
						required
						autocomplete="current-password">
				</p>

				<p>

					<button
						type="submit"
						name="login"
						id="loginbutton">
						Kirjaudu
					</button>

					<a
						href="register.php?skey=<?= urlencode((string)$_GET['skey']) ?>"
						id="registerbutton">
						Rekisteröidy
					</a>
				</p>

			</form>

		</div>

	</div>

</body>

</html>