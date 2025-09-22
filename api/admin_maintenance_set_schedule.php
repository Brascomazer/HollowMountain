<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

require_method('POST');
$user = require_auth();
if ($user['role'] !== 'Administrator') {
	json(['error' => 'Forbidden'], 403);
}

$payload = require_json();
$attractionId = (int)($payload['attraction_id'] ?? 0);
$frequency = (string)($payload['frequency'] ?? '');
$nextDate = (string)($payload['next_date'] ?? ''); // YYYY-MM-DD
$notes = (string)($payload['notes'] ?? '');

if ($attractionId <= 0 || !in_array($frequency, ['daily','weekly','monthly','quarterly','yearly'], true) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $nextDate)) {
	json(['error' => 'Invalid input'], 400);
}

// Upsert schedule (one schedule per attraction)
$pdo = db();
$pdo->beginTransaction();
$pdo->prepare('DELETE FROM maintenance_schedules WHERE attraction_id = :id')->execute([':id' => $attractionId]);
$stmt = $pdo->prepare('INSERT INTO maintenance_schedules (attraction_id, frequency, next_date, notes) VALUES (:a,:f,:d,:n)');
$stmt->execute([':a' => $attractionId, ':f' => $frequency, ':d' => $nextDate, ':n' => $notes]);
$pdo->commit();

json(['ok' => true]);


