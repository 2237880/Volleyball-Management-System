<?php
declare(strict_types=1);

// Run from CLI:
//   php database/seed_demo.php
//
// It will insert demo users/teams/players/matches and a completed match score
// (if your DB tables exist).

require_once __DIR__ . '/../app/lib/Database.php';

$pdo = Database::pdo();

function nowShift(string $shift): string
{
    $dt = new DateTime($shift);
    return $dt->format('Y-m-d H:i:s');
}

function upsertUser(int $idExisting, PDO $pdo, string $name, string $email, string $password, string $role): int
{
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $row = $stmt->fetch();
    if ($row && isset($row['id'])) {
        return (int)$row['id'];
    }

    $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :password_hash, :role)');
    $stmt->execute([
        'name' => $name,
        'email' => $email,
        'password_hash' => $hash,
        'role' => $role,
    ]);

    return (int)$pdo->lastInsertId();
}

function upsertTeam(PDO $pdo, string $name, ?int $coachUserId): int
{
    $stmt = $pdo->prepare('SELECT id FROM teams WHERE name = :name LIMIT 1');
    $stmt->execute(['name' => $name]);
    $row = $stmt->fetch();
    if ($row && isset($row['id'])) {
        return (int)$row['id'];
    }

    $stmt = $pdo->prepare('INSERT INTO teams (name, coach_user_id) VALUES (:name, :coach_user_id)');
    $stmt->execute(['name' => $name, 'coach_user_id' => $coachUserId]);
    return (int)$pdo->lastInsertId();
}

function upsertPlayer(PDO $pdo, int $teamId, int $playerUserId, string $position, int $isCaptain): void
{
    $stmt = $pdo->prepare('SELECT id FROM players WHERE team_id = :team_id AND player_user_id = :player_user_id LIMIT 1');
    $stmt->execute(['team_id' => $teamId, 'player_user_id' => $playerUserId]);
    $row = $stmt->fetch();

    if ($row && isset($row['id'])) {
        $stmt = $pdo->prepare('UPDATE players SET position = :position, is_captain = :is_captain WHERE team_id = :team_id AND player_user_id = :player_user_id');
        $stmt->execute([
            'position' => $position,
            'is_captain' => $isCaptain,
            'team_id' => $teamId,
            'player_user_id' => $playerUserId,
        ]);
        return;
    }

    $stmt = $pdo->prepare('INSERT INTO players (team_id, player_user_id, position, is_captain) VALUES (:team_id, :player_user_id, :position, :is_captain)');
    $stmt->execute([
        'team_id' => $teamId,
        'player_user_id' => $playerUserId,
        'position' => $position,
        'is_captain' => $isCaptain,
    ]);
}

function upsertMatch(PDO $pdo, int $homeTeamId, int $awayTeamId, string $scheduledAt, ?int $createdBy): int
{
    $stmt = $pdo->prepare('
        SELECT id
        FROM matches
        WHERE home_team_id = :home_team_id
          AND away_team_id = :away_team_id
          AND scheduled_at = :scheduled_at
        LIMIT 1
    ');
    $stmt->execute([
        'home_team_id' => $homeTeamId,
        'away_team_id' => $awayTeamId,
        'scheduled_at' => $scheduledAt,
    ]);
    $row = $stmt->fetch();
    if ($row && isset($row['id'])) {
        return (int)$row['id'];
    }

    $stmt = $pdo->prepare('
        INSERT INTO matches (home_team_id, away_team_id, scheduled_at, created_by)
        VALUES (:home_team_id, :away_team_id, :scheduled_at, :created_by)
    ');
    $stmt->execute([
        'home_team_id' => $homeTeamId,
        'away_team_id' => $awayTeamId,
        'scheduled_at' => $scheduledAt,
        'created_by' => $createdBy,
    ]);
    return (int)$pdo->lastInsertId();
}

function upsertScore(PDO $pdo, int $matchId, int $homeScore, int $awayScore, ?int $winnerTeamId, ?int $enteredBy): void
{
    $stmt = $pdo->prepare('SELECT match_id FROM match_scores WHERE match_id = :match_id LIMIT 1');
    $stmt->execute(['match_id' => $matchId]);
    $row = $stmt->fetch();

    if ($row) {
        $stmt = $pdo->prepare('
            UPDATE match_scores
            SET home_score = :home_score,
                away_score = :away_score,
                winner_team_id = :winner_team_id,
                entered_by = :entered_by,
                updated_at = NOW()
            WHERE match_id = :match_id
        ');
        $stmt->execute([
            'home_score' => $homeScore,
            'away_score' => $awayScore,
            'winner_team_id' => $winnerTeamId,
            'entered_by' => $enteredBy,
            'match_id' => $matchId,
        ]);
        return;
    }

    $stmt = $pdo->prepare('
        INSERT INTO match_scores (match_id, home_score, away_score, winner_team_id, entered_by)
        VALUES (:match_id, :home_score, :away_score, :winner_team_id, :entered_by)
    ');
    $stmt->execute([
        'match_id' => $matchId,
        'home_score' => $homeScore,
        'away_score' => $awayScore,
        'winner_team_id' => $winnerTeamId,
        'entered_by' => $enteredBy,
    ]);
}

// Demo accounts (edit if you want).
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

try {
    $pdo->beginTransaction();

    $adminId = upsertUser(0, $pdo, $demo['admin']['name'], $demo['admin']['email'], $demo['admin']['pass'], 'admin');
    $coachId = upsertUser(0, $pdo, $demo['coach']['name'], $demo['coach']['email'], $demo['coach']['pass'], 'coach');

    $teamAId = upsertTeam($pdo, 'Team A', $coachId);
    $teamBId = upsertTeam($pdo, 'Team B', $coachId);

    // Players: 2 players each team.
    $p1 = $demo['players'][0];
    $p2 = $demo['players'][1];
    $p3 = $demo['players'][2];
    $p4 = $demo['players'][3];

    $p1Id = upsertUser(0, $pdo, $p1['name'], $p1['email'], $p1['pass'], 'player');
    $p2Id = upsertUser(0, $pdo, $p2['name'], $p2['email'], $p2['pass'], 'player');
    $p3Id = upsertUser(0, $pdo, $p3['name'], $p3['email'], $p3['pass'], 'player');
    $p4Id = upsertUser(0, $pdo, $p4['name'], $p4['email'], $p4['pass'], 'player');

    upsertPlayer($pdo, $teamAId, $p1Id, 'Setter', 1);
    upsertPlayer($pdo, $teamAId, $p2Id, 'Libero', 0);
    upsertPlayer($pdo, $teamBId, $p3Id, 'Spiker', 1);
    upsertPlayer($pdo, $teamBId, $p4Id, 'Middle', 0);

    // Matches: one completed (past) and one upcoming (future).
    $completedAt = nowShift('-2 days');
    $upcomingAt = nowShift('+2 days');

    $completedMatchId = upsertMatch($pdo, $teamAId, $teamBId, $completedAt, $adminId);
    $upcomingMatchId = upsertMatch($pdo, $teamBId, $teamAId, $upcomingAt, $coachId);

    // Completed match score: Team A wins (e.g., 3-1).
    upsertScore($pdo, $completedMatchId, 3, 1, $teamAId, $coachId);

    $pdo->commit();

    echo "Demo data seeded successfully.\n";
    echo "Login credentials:\n";
    echo "- Admin:    {$demo['admin']['email']} / {$demo['admin']['pass']}\n";
    echo "- Coach:    {$demo['coach']['email']} / {$demo['coach']['pass']}\n";
    echo "- Players:  {$demo['players'][0]['email']} / {$demo['players'][0]['pass']} (and others)\n";
    echo "\nMatch IDs:\n";
    echo "- Completed: {$completedMatchId}\n";
    echo "- Upcoming:  {$upcomingMatchId}\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Seeding failed: " . $e->getMessage() . "\n";
    exit(1);
}

