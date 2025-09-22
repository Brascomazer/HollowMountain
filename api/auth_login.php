<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

require_method('POST');
$payload = require_json();

$email = strtolower(trim($payload['email'] ?? ''));
$password = (string)($payload['password'] ?? '');
if ($email === '' || $password === '') {
	json(['error' => 'Email en wachtwoord zijn vereist'], 400);
}

$stmt = db()->prepare('SELECT id, name, email, password_hash, role FROM users WHERE email = :email LIMIT 1');
$stmt->execute([':email' => $email]);
$user = $stmt->fetch();
if (!$user || !password_verify($password, $user['password_hash'])) {
	json(['error' => 'Ongeldige inloggegevens'], 401);
}

$_SESSION['user'] = [
	'id' => (int)$user['id'],
	'name' => $user['name'],
	'email' => $user['email'],
	'role' => $user['role'],
];

json(['user' => $_SESSION['user']]);


