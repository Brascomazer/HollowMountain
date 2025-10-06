<?php
// Basic configuration for database and sessions

declare(strict_types=1);

// Adjust these to your local MySQL setup
$DB_HOST = '127.0.0.1';
$DB_NAME = 'hollow_mountain';
$DB_USER = 'root';
$DB_PASS = '';
$DB_CHARSET = 'utf8mb4';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CORS for local dev (allow requests from static servers like 127.0.0.1:5500)
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
} else {
    header('Access-Control-Allow-Origin: *');
}
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Ensure errors surface as JSON, not HTML
error_reporting(E_ALL);
ini_set('display_errors', '0');

set_exception_handler(function (Throwable $e): void {
	json([
		'error' => 'Server error',
		'detail' => $e->getMessage(),
	], 500);
});

set_error_handler(function (int $severity, string $message, string $file = '', int $line = 0): bool {
	if (!(error_reporting() & $severity)) {
		return false;
	}
	throw new ErrorException($message, 0, $severity, $file, $line);
});

function db(): PDO {
	global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS, $DB_CHARSET;
	static $pdo = null;
	if ($pdo === null) {
		$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset={$DB_CHARSET}";
		$options = [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES => false,
		];
		$pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
	}
	return $pdo;
}

function json($data, int $status = 200): void {
	header('Content-Type: application/json');
	http_response_code($status);
	echo json_encode($data);
	exit;
}

function require_method(string $method): void {
	if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== strtoupper($method)) {
		json(['error' => 'Method Not Allowed'], 405);
	}
}

function require_json(): array {
	$raw = file_get_contents('php://input');
	$payload = json_decode($raw, true);
	if (!is_array($payload)) {
		json(['error' => 'Invalid JSON'], 400);
	}
	return $payload;
}

function current_user(): ?array {
	return $_SESSION['user'] ?? null;
}

function require_auth(): array {
	$user = current_user();
	if (!$user) {
		json(['error' => 'Unauthorized'], 401);
	}
	return $user;
}


