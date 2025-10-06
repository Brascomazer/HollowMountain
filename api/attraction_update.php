<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

require_method('POST');
$user = require_auth();
if ($user['role'] !== 'Administrator') {
	json(['error' => 'Forbidden'], 403);
}

$payload = require_json();
$id = (int)($payload['id'] ?? 0);
$name = trim((string)($payload['name'] ?? ''));
$location = trim((string)($payload['location'] ?? ''));
$type = trim((string)($payload['type'] ?? ''));

if ($id <= 0 || $name === '' || $location === '' || $type === '') {
    json(['error' => 'id, name, location, type required'], 400);
}

$stmt = db()->prepare('UPDATE attractions SET name = :n, location = :l, type = :t WHERE id = :id');
$stmt->execute([':n' => $name, ':l' => $location, ':t' => $type, ':id' => $id]);

json(['ok' => true]);


