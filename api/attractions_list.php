<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

require_method('GET');

$stmt = db()->query('SELECT id, name, location, type FROM attractions ORDER BY id DESC');
$rows = $stmt->fetchAll();

json(['items' => $rows]);


