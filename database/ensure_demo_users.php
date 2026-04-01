<?php
declare(strict_types=1);

// Ensures demo user accounts exist with known passwords.
// Run (from CLI):
//   php database/ensure_demo_users.php
//
// This does NOT create teams/matches/players roster; it only fixes login credentials.

require_once __DIR__ . '/../app/lib/Database.php';

$pdo = Database::pdo();

$demo = [
    'admin' => ['name' => 'Demo Admin', 'email' => 'admin@demo.local', 'pass' => 'Admin123!'],
    'coach' => ['name' => 'Demo Coach', 'email' => 'coach@demo.local', 'pass' => 'Coach123!'],
    'players' => [
        ['name' => 'Player One', 'email' => 'p1@demo.local', 'pass' => 'Player123!'],
        ['name' => 'Player Two', 'email' => 'p2@demo.local', 'pass' => 'Player123!'],
        ['name' => 'Player Three', 'email' => 'p3@demo.local', 'pass' => 'Player123!'],
        ['name' => 'Player Four', 'email' => 'p4@demo.local', 'pass' => 'Player123!'],
    ],
];

function upsertUser(PDO $pdo, string $name, string $email, string $password, string $role): void
{
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email AND role = :role LIMIT 1');
    $stmt->execute(['email' => $email, 'role' => $role]);
    $row = $stmt->fetch();

    if ($row) {
        $upd = $pdo->prepare('UPDATE users SET name = :name, password_hash = :password_hash WHERE email = :email AND role = :role');
        $upd->execute([
            'name' => $name,
            'password_hash' => $hash,
            'email' => $email,
            'role' => $role,
        ]);
        echo "Updated {$role}: {$email}\n";
        return;
    }

    // If email exists under a different role (unlikely, but possible), create a new user row will fail due to UNIQUE email.
    // In that case we update the existing email with this role.
    $stmt2 = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt2->execute(['email' => $email]);
    $row2 = $stmt2->fetch();

    if ($row2) {
        $upd = $pdo->prepare('UPDATE users SET name = :name, password_hash = :password_hash, role = :role WHERE email = :email');
        $upd->execute([
            'name' => $name,
            'password_hash' => $hash,
            'role' => $role,
            'email' => $email,
        ]);
        echo "Re-role+Updated {$role}: {$email}\n";
        return;
    }

    $ins = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :password_hash, :role)');
    $ins->execute([
        'name' => $name,
        'email' => $email,
        'password_hash' => $hash,
        'role' => $role,
    ]);
    echo "Inserted {$role}: {$email}\n";
}

try {
    $pdo->beginTransaction();

    upsertUser($pdo, $demo['admin']['name'], $demo['admin']['email'], $demo['admin']['pass'], 'admin');
    upsertUser($pdo, $demo['coach']['name'], $demo['coach']['email'], $demo['coach']['pass'], 'coach');
    foreach ($demo['players'] as $p) {
        upsertUser($pdo, $p['name'], $p['email'], $p['pass'], 'player');
    }

    $pdo->commit();
    echo "Done.\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Failed: " . $e->getMessage() . "\n";
    exit(1);
}

