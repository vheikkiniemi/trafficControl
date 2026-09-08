<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

$conn = OpenCon();

$stmt = $conn->query(
    'SELECT koodi, maarittely
     FROM infot
     ORDER BY koodi'
);

$rows = $stmt->fetchAll();

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
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Legacy PHP Modernization</title>

    <style>
        body {
            font-family: system-ui, sans-serif;
            max-width: 900px;
            margin: 3rem auto;
            padding: 0 1rem;
            line-height: 1.5;
        }

        h1 {
            margin-bottom: 0.5rem;
        }

        .status {
            padding: 1rem;
            margin: 1.5rem 0;
            background: #f3f3f3;
            border-radius: 0.5rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
        }

        th,
        td {
            padding: 0.75rem;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #f7f7f7;
        }

        code {
            background: #f3f3f3;
            padding: 0.15rem 0.35rem;
            border-radius: 0.25rem;
        }
    </style>
</head>

<body>

    <h1>PHP + PostgreSQL container environment</h1>

    <div class="status">
        PHP successfully connected to PostgreSQL.
        Found <strong><?= count($rows) ?></strong> rows in
        <code>infot</code>.
    </div>

    <?php if ($rows): ?>

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
                        <td><?= e((string)$row['koodi']) ?></td>
                        <td><?= e((string)$row['maarittely']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php else: ?>

        <p>No rows found in the <code>infot</code> table.</p>

    <?php endif; ?>

</body>
</html>

<?php
CloseCon($conn);
?>