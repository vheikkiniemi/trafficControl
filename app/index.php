<?php

declare(strict_types=1);

require __DIR__ . '/db.php';

$stmt = $pdo->query('SELECT koodi, maarittely FROM infot ORDER BY koodi');
$rows = $stmt->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Legacy PHP Modernization</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 900px; margin: 3rem auto; padding: 0 1rem; }
        table { border-collapse: collapse; width: 100%; }
        th, td { padding: .7rem; border-bottom: 1px solid #ddd; text-align: left; }
        code { background: #f3f3f3; padding: .15rem .35rem; border-radius: .25rem; }
    </style>
</head>
<body>
    <h1>PHP + PostgreSQL container environment</h1>
    <p>If you can see the rows below, PHP and PostgreSQL are communicating correctly.</p>

    <table>
        <thead>
            <tr>
                <th>Koodi</th>
                <th>Määrittely</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['koodi']) ?></td>
                <td><?= htmlspecialchars($row['maarittely']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
