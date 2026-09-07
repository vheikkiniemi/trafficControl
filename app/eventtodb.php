<?php

date_default_timezone_set("Europe/Helsinki");

require_once("db.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit;
}


/*
|--------------------------------------------------------------------------
| Validate required POST parameters
|--------------------------------------------------------------------------
*/

$requiredFields = [
    "nodeId",
    "password",
    "event",
    "value",
    "msgId",
    "time"
];

foreach ($requiredFields as $field) {
    if (!isset($_POST[$field]) || $_POST[$field] === "") {
        exit;
    }
}


/*
|--------------------------------------------------------------------------
| Validate timestamp
|--------------------------------------------------------------------------
|
| Accept messages only when the timestamp differs from server time
| by less than 3 seconds.
|
*/

$timestamp = (int)$_POST["time"];

if (abs(time() - $timestamp) >= 3) {
    exit;
}


/*
|--------------------------------------------------------------------------
| Open PostgreSQL connection
|--------------------------------------------------------------------------
*/

$conn = OpenCon();


/*
|--------------------------------------------------------------------------
| Authenticate node
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT nodekey, password
    FROM nodes
    WHERE nodeid = :nodeid
";

$stmt = $conn->prepare($sql);

$stmt->execute([
    "nodeid" => $_POST["nodeId"]
]);

$node = $stmt->fetch();

if (!$node) {
    CloseCon($conn);
    exit;
}


/*
|--------------------------------------------------------------------------
| Verify HMAC signature
|--------------------------------------------------------------------------
*/

$expectedHash = hash_hmac(
    "sha256",
    (string)$timestamp,
    $node["nodekey"]
);

if (!hash_equals($expectedHash, $_POST["msgId"])) {
    CloseCon($conn);
    exit;
}


/*
|--------------------------------------------------------------------------
| Verify node password
|--------------------------------------------------------------------------
*/

if (!password_verify($_POST["password"], $node["password"])) {
    CloseCon($conn);
    exit;
}


/*
|--------------------------------------------------------------------------
| Store event
|--------------------------------------------------------------------------
*/

$sensor = isset($_POST["sensor"])
    ? $_POST["sensor"]
    : "";

$sql = "
    INSERT INTO liikennevalot_nodes_events
        (node_id, event, time, value, sensor)
    VALUES
        (:node_id, :event, :time, :value, :sensor)
";

$stmt = $conn->prepare($sql);

$stmt->execute([
    "node_id" => $_POST["nodeId"],
    "event"   => $_POST["event"],
    "time"    => time(),
    "value"   => $_POST["value"],
    "sensor"  => $sensor
]);

echo "OK";


/*
|--------------------------------------------------------------------------
| Close connection
|--------------------------------------------------------------------------
*/

CloseCon($conn);
?>