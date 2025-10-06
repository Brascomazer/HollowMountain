<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

require_method('POST');

$payload = require_json();
$name = trim((string)($payload['name'] ?? ''));
$location = trim((string)($payload['location'] ?? ''));
$type = trim((string)($payload['type'] ?? ''));
$photos = $payload['photos'] ?? null; // array of URLs
$tech = $payload['technical_specs'] ?? null; // object

if ($name === '' || $location === '' || $type === '') {
	json(['error' => 'name, location, type are required'], 400);
}

$stmt = db()->prepare('INSERT INTO attractions (name, location, type, photos, technical_specs) VALUES (:n,:l,:t,:p,:s)');
$stmt->execute([
	':n' => $name,
	':l' => $location,
	':t' => $type,
	':p' => $photos ? json_encode($photos) : null,
	':s' => $tech ? json_encode($tech) : null,
]);

$id = (int)db()->lastInsertId();
json(['id' => $id]);


