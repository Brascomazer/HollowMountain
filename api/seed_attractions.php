<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

// Seed demo attractions if none exist yet
$pdo = db();
$count = (int)$pdo->query('SELECT COUNT(*) FROM attractions')->fetchColumn();
if ($count === 0) {
    $stmt = $pdo->prepare('INSERT INTO attractions (name, location, type) VALUES (:n,:l,:t)');
    $items = [
        ['Dragon Coaster', 'Thrill Zone', 'Roller Coaster'],
        ['Ferris Wheel', 'Central Plaza', 'Family Ride'],
        ['Haunted House', 'Spooky Alley', 'Dark Ride'],
    ];
    foreach ($items as $a) {
        $stmt->execute([':n' => $a[0], ':l' => $a[1], ':t' => $a[2]]);
    }
    json(['seeded' => 3]);
}

json(['seeded' => 0]);


