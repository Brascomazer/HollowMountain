<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

function ensure_demo_users(): void {
	$pdo = db();
	// Check if the admin user exists
	$stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
	$stmt->execute([':email' => 'admin@hm.local']);
	$exists = $stmt->fetch();
	if ($exists) {
		return;
	}
	// Insert demo users with hashed passwords
	$users = [
		['Admin User', 'admin@hm.local', 'Admin123!', 'Administrator'],
		['Manager User', 'manager@hm.local', 'Manager123!', 'Manager'],
		['Worker User', 'worker@hm.local', 'Worker123!', 'Medewerker'],
	];
	$ins = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (:n,:e,:h,:r)');
	foreach ($users as $u) {
		$ins->execute([
			':n' => $u[0],
			':e' => $u[1],
			':h' => password_hash($u[2], PASSWORD_BCRYPT),
			':r' => $u[3],
		]);
	}
}

ensure_demo_users();


