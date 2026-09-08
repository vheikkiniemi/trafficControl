<?php
date_default_timezone_set("Europe/Helsinki");
session_start();
require_once('session.php');
$_SESSION["index"] = true;
// Leikkiä idn kanssa
$_SESSION["id"] = hash('sha256', rand(1, 10));
?>

<!DOCTYPE html>
<html lang="fi">
<head>
<script src="jquery-3.3.1.min.js"></script>
<script src="index.js"></script>
<link rel="StyleSheet" href="index.css" type="text/css" />
<link rel="icon" href="data:image/png;base64,iVBORw0KGgo=" type="image/png">
<meta charset="utf-8" />
<title>Liikennevalojen tapahtumat</title></head>
<body>
<div id="center">
<div id="header">
Liikennevalojen tapahtumat
</div>
<div id="painikkeet">
<?php
// Linkit muokkautuu autentikoinnin mukaan
if($omatunnus == "0") {
	echo "<a href=\"login.php?skey=" . hash_hmac('sha256', $_SESSION["id"], $key) . "\"><div id=\"nologin\" class=\"nologin\">OHJAUS - VAATII KIRJAUTUMISEN</div></a>";
	echo "<a href=\"login.php?skey=" . hash_hmac('sha256', $_SESSION["id"], $key) . "\"><div id=\"login\">KIRJAUDU</div></a>";
} else {
	$oKey = hash('sha256', rand(1, 10));
	echo "<div id=\"onlogin\" onmousedown=\"ohjaus('" . hash_hmac('sha256', $oKey, $key) . "', '" . $oKey . "')\">OHJAA</div>";
	echo "<a href=\"logoff.php?skey=" . hash_hmac('sha256', $_SESSION["id"], $key) . "\"><div id=\"logoff\">KIRJAUDU ULOS</div></a>";
}
?>
</div>
<div id="tapahtumat">
<!-- Tapahtuma-taulu, johon tuodaan tiedot päätietokannasta -->
<table id='tapahtumat_tb'><tr><th id='aika'>AIKA</th><th id='tapahtuma'>TAPAHTUMA</th></tr>
<?php
include 'haekanta.php';
?>
</table>
</div>
<!-- Jos tapahtuu virheitä -->
<div id="status"></div>
</div>
</body>
</html>