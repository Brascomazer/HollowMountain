<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

require_method('POST');
$user = require_auth();
if ($user['role'] !== 'Administrator') {
	json(['error' => 'Forbidden'], 403);
}

$payload = require_json();
$taskId = (int)($payload['task_id'] ?? 0);
$userId = (int)($payload['user_id'] ?? 0);

if ($taskId <= 0 || $userId <= 0) {
	json(['error' => 'task_id and user_id required'], 400);
}

$stmt = db()->prepare('UPDATE maintenance_tasks SET assigned_to = :u WHERE id = :t');
$stmt->execute([':u' => $userId, ':t' => $taskId]);

json(['ok' => true]);


