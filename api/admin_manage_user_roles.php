<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

// Alleen managers en administrators kunnen rollen wijzigen
require_manager_or_admin();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Haal alle gebruikers op voor de rol management interface
    $stmt = db()->prepare('SELECT id, name, email, role, employee_id, created_at FROM users ORDER BY name');
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    json(['users' => $users]);
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $payload = require_json();
    
    $user_id = (int)($payload['user_id'] ?? 0);
    $new_role = trim($payload['role'] ?? '');
    
    if ($user_id <= 0) {
        json(['error' => 'Ongeldige gebruiker ID'], 400);
    }
    
    $allowed_roles = ['Administrator', 'Manager', 'Monteur', 'Medewerker'];
    if (!in_array($new_role, $allowed_roles, true)) {
        json(['error' => 'Ongeldige rol. Toegestane rollen: ' . implode(', ', $allowed_roles)], 400);
    }
    
    // Controleer of de gebruiker bestaat
    $stmt = db()->prepare('SELECT id, name, email, role FROM users WHERE id = :id');
    $stmt->execute([':id' => $user_id]);
    $target_user = $stmt->fetch();
    
    if (!$target_user) {
        json(['error' => 'Gebruiker niet gevonden'], 404);
    }
    
    // Voorkom dat een gebruiker zijn eigen rol wijzigt naar een lagere rol
    $current_user = current_user();
    if ($current_user['id'] === $user_id && $current_user['role'] === 'Administrator' && $new_role !== 'Administrator') {
        json(['error' => 'Je kunt je eigen administrator rol niet wijzigen'], 403);
    }
    
    // Update de rol
    $stmt = db()->prepare('UPDATE users SET role = :role, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
    $stmt->execute([
        ':role' => $new_role,
        ':id' => $user_id
    ]);
    
    json([
        'success' => true,
        'message' => "Rol van {$target_user['name']} succesvol gewijzigd naar {$new_role}",
        'user' => [
            'id' => $target_user['id'],
            'name' => $target_user['name'],
            'email' => $target_user['email'],
            'role' => $new_role
        ]
    ]);
}

json(['error' => 'Method Not Allowed'], 405);