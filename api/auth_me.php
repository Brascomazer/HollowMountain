<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

require_method('GET');
$user = current_user();
if (!$user) {
	json(['user' => null]);
}
json(['user' => $user]);


