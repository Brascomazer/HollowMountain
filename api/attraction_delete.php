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
if ($id <= 0) {
    json(['error' => 'id required'], 400);
}

$stmt = db()->prepare('DELETE FROM attractions WHERE id = :id');
$stmt->execute([':id' => $id]);

json(['ok' => true]);


