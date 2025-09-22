<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

$passwords = [
	'admin@hm.local' => 'Admin123!',
	'manager@hm.local' => 'Manager123!',
	'worker@hm.local' => 'Worker123!',
];

$pdo = db();
foreach ($passwords as $email => $plain) {
	$hash = password_hash($plain, PASSWORD_BCRYPT);
	$stmt = $pdo->prepare('UPDATE users SET password_hash = :hash WHERE email = :email');
	$stmt->execute([':hash' => $hash, ':email' => $email]);
}

echo "Seeded password hashes.\n";


