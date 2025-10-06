<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

// Alleen managers en administrators kunnen nieuwe medewerkers registreren
require_manager_or_admin();
require_method('POST');

$payload = require_json();

// Valideer vereiste velden
$name = trim($payload['name'] ?? '');
$email = strtolower(trim($payload['email'] ?? ''));
$role = trim($payload['role'] ?? '');
$password = trim($payload['password'] ?? '');

if ($name === '' || $email === '' || $role === '' || $password === '') {
    json(['error' => 'Naam, e-mail, rol en wachtwoord zijn verplicht'], 400);
}

// Valideer e-mail formaat
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json(['error' => 'Ongeldig e-mailadres'], 400);
}

// Valideer rol
$allowed_roles = ['Administrator', 'Manager', 'Monteur', 'Medewerker'];
if (!in_array($role, $allowed_roles, true)) {
    json(['error' => 'Ongeldige rol. Toegestane rollen: ' . implode(', ', $allowed_roles)], 400);
}

// Valideer wachtwoord sterkte
if (strlen($password) < 6) {
    json(['error' => 'Wachtwoord moet minimaal 6 karakters lang zijn'], 400);
}

// Optionele velden
$phone = trim($payload['phone'] ?? '');
$address_street = trim($payload['address_street'] ?? '');
$address_number = trim($payload['address_number'] ?? '');
$address_city = trim($payload['address_city'] ?? '');
$address_postal_code = trim($payload['address_postal_code'] ?? '');
$address_country = trim($payload['address_country'] ?? 'Nederland');
$date_of_birth = trim($payload['date_of_birth'] ?? '');

// Valideer geboortedatum indien opgegeven
if ($date_of_birth !== '' && !DateTime::createFromFormat('Y-m-d', $date_of_birth)) {
    json(['error' => 'Ongeldige geboortedatum. Gebruik formaat YYYY-MM-DD'], 400);
}

try {
    // Controleer of e-mail al bestaat
    $stmt = db()->prepare('SELECT id FROM users WHERE email = :email');
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        json(['error' => 'E-mailadres is al in gebruik'], 409);
    }
    
    // Genereer uniek medewerker ID
    $employee_id = generate_employee_id();
    
    // Controleer of employee_id uniek is
    $attempts = 0;
    while ($attempts < 10) {
        $stmt = db()->prepare('SELECT id FROM users WHERE employee_id = :employee_id');
        $stmt->execute([':employee_id' => $employee_id]);
        if (!$stmt->fetch()) {
            break;
        }
        $employee_id = generate_employee_id();
        $attempts++;
    }
    
    if ($attempts >= 10) {
        json(['error' => 'Kon geen uniek medewerker ID genereren'], 500);
    }
    
    // Hash wachtwoord
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Voeg nieuwe medewerker toe
    $stmt = db()->prepare('
        INSERT INTO users (
            name, email, password_hash, role, phone, 
            address_street, address_number, address_city, address_postal_code, address_country,
            date_of_birth, employee_id
        ) VALUES (
            :name, :email, :password_hash, :role, :phone,
            :address_street, :address_number, :address_city, :address_postal_code, :address_country,
            :date_of_birth, :employee_id
        )
    ');
    
    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':password_hash' => $password_hash,
        ':role' => $role,
        ':phone' => $phone ?: null,
        ':address_street' => $address_street ?: null,
        ':address_number' => $address_number ?: null,
        ':address_city' => $address_city ?: null,
        ':address_postal_code' => $address_postal_code ?: null,
        ':address_country' => $address_country,
        ':date_of_birth' => $date_of_birth ?: null,
        ':employee_id' => $employee_id
    ]);
    
    $new_user_id = (int)db()->lastInsertId();
    
    // Haal de nieuwe gebruiker op (zonder wachtwoord hash)
    $stmt = db()->prepare('
        SELECT id, name, email, role, phone, 
               address_street, address_number, address_city, address_postal_code, address_country,
               date_of_birth, employee_id, created_at
        FROM users 
        WHERE id = :id
    ');
    $stmt->execute([':id' => $new_user_id]);
    $new_user = $stmt->fetch();
    
    json([
        'success' => true,
        'message' => "Medewerker {$name} succesvol geregistreerd",
        'user' => $new_user,
        'temporary_password' => $password // In productie zou je dit via een veilige methode verzenden
    ]);
    
} catch (Exception $e) {
    json(['error' => 'Fout bij registreren van medewerker: ' . $e->getMessage()], 500);
}