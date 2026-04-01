<?php
declare(strict_types=1);

final class TeamModel
{
    public static function getTeamsForUser(int $userId, string $role): array
    {
        if ($role === 'admin' || $role === 'coach') {
            if ($role === 'coach') {
                return Database::fetchAll(
                    'SELECT t.*,
                            c.name AS coach_name
                     FROM teams t
                     LEFT JOIN users c ON c.id = t.coach_user_id
                     WHERE t.coach_user_id = :coach_id
                     ORDER BY t.id DESC',
                    ['coach_id' => $userId]
                );
            }

            return Database::fetchAll(
                'SELECT t.*,
                        c.name AS coach_name
                 FROM teams t
                 LEFT JOIN users c ON c.id = t.coach_user_id
                 ORDER BY t.id DESC'
            );
        }

        // Player: only their team(s)
        return Database::fetchAll(
            'SELECT DISTINCT t.*,
                    c.name AS coach_name
             FROM players p
             INNER JOIN teams t ON t.id = p.team_id
             LEFT JOIN users c ON c.id = t.coach_user_id
             WHERE p.player_user_id = :player_user_id
             ORDER BY t.id DESC',
            ['player_user_id' => $userId]
        );
    }

    public static function getTeamById(int $teamId): ?array
    {
        return Database::fetchOne(
            'SELECT t.*,
                    c.name AS coach_name
             FROM teams t
             LEFT JOIN users c ON c.id = t.coach_user_id
             WHERE t.id = :team_id
             LIMIT 1',
            ['team_id' => $teamId]
        );
    }

    public static function getCoaches(): array
    {
        return Database::fetchAll(
            'SELECT id, name, email FROM users WHERE role = :role ORDER BY name',
            ['role' => 'coach']
        );
    }

    public static function getPlayersNotInTeam(int $teamId): array
    {
        // Players not already assigned to team.
        return Database::fetchAll(
            'SELECT u.id, u.name, u.email
             FROM users u
             WHERE u.role = :player_role
               AND u.id NOT IN (SELECT p.player_user_id FROM players p WHERE p.team_id = :team_id)
             ORDER BY u.name',
            [
                'player_role' => 'player',
                'team_id' => $teamId,
            ]
        );
    }

    public static function getRoster(int $teamId): array
    {
        return Database::fetchAll(
            'SELECT p.player_user_id,
                    u.name AS player_name,
                    u.email,
                    p.position,
                    p.is_captain
             FROM players p
             INNER JOIN users u ON u.id = p.player_user_id
             WHERE p.team_id = :team_id
             ORDER BY p.is_captain DESC, u.name',
            ['team_id' => $teamId]
        );
    }

    public static function isTeamAccessibleToUser(int $teamId, int $userId, string $role): bool
    {
        if ($role === 'admin') {
            return true;
        }

        if ($role === 'coach') {
            $row = Database::fetchOne(
                'SELECT id FROM teams WHERE id = :team_id AND coach_user_id = :user_id LIMIT 1',
                ['team_id' => $teamId, 'user_id' => $userId]
            );
            return $row !== null;
        }

        if ($role === 'player') {
            $row = Database::fetchOne(
                'SELECT id FROM players WHERE team_id = :team_id AND player_user_id = :user_id LIMIT 1',
                ['team_id' => $teamId, 'user_id' => $userId]
            );
            return $row !== null;
        }

        return false;
    }

    public static function createTeam(string $name, int $coachUserId): int
    {
        Database::execute(
            'INSERT INTO teams (name, coach_user_id) VALUES (:name, :coach_user_id)',
            ['name' => $name, 'coach_user_id' => $coachUserId]
        );

        $row = Database::fetchOne('SELECT LAST_INSERT_ID() AS id');
        return (int)$row['id'];
    }

    public static function updateTeam(int $teamId, string $name, ?int $coachUserId): void
    {
        Database::execute(
            'UPDATE teams
             SET name = :name,
                 coach_user_id = :coach_user_id
             WHERE id = :team_id',
            [
                'name' => $name,
                'coach_user_id' => $coachUserId,
                'team_id' => $teamId,
            ]
        );
    }

    public static function addPlayer(int $teamId, int $playerUserId, string $position): bool
    {
        // Player uniqueness is enforced by UNIQUE(team_id, player_user_id).
        $existing = Database::fetchOne(
            'SELECT id FROM players WHERE team_id = :team_id AND player_user_id = :player_user_id LIMIT 1',
            ['team_id' => $teamId, 'player_user_id' => $playerUserId]
        );
        if ($existing) {
            return false;
        }

        Database::execute(
            'INSERT INTO players (team_id, player_user_id, position, is_captain)
             VALUES (:team_id, :player_user_id, :position, 0)',
            [
                'team_id' => $teamId,
                'player_user_id' => $playerUserId,
                'position' => $position,
            ]
        );
        return true;
    }

    public static function removePlayer(int $teamId, int $playerUserId): void
    {
        $isCaptain = Database::fetchOne(
            'SELECT is_captain FROM players WHERE team_id = :team_id AND player_user_id = :player_user_id LIMIT 1',
            ['team_id' => $teamId, 'player_user_id' => $playerUserId]
        );

        Database::execute(
            'DELETE FROM players WHERE team_id = :team_id AND player_user_id = :player_user_id',
            ['team_id' => $teamId, 'player_user_id' => $playerUserId]
        );

        // If we removed the captain, ensure there's still exactly one captain if any players remain.
        if ($isCaptain && (int)$isCaptain['is_captain'] === 1) {
            $row = Database::fetchOne(
                'SELECT player_user_id
                 FROM players
                 WHERE team_id = :team_id
                 ORDER BY id ASC
                 LIMIT 1',
                ['team_id' => $teamId]
            );

            if ($row) {
                Database::execute(
                    'UPDATE players
                     SET is_captain = CASE WHEN player_user_id = :new_captain THEN 1 ELSE 0 END
                     WHERE team_id = :team_id',
                    ['new_captain' => (int)$row['player_user_id'], 'team_id' => $teamId]
                );
            }
        }
    }

    public static function setCaptain(int $teamId, int $playerUserId): bool
    {
        // Ensure the player belongs to the team.
        $belongs = Database::fetchOne(
            'SELECT id FROM players WHERE team_id = :team_id AND player_user_id = :player_user_id LIMIT 1',
            ['team_id' => $teamId, 'player_user_id' => $playerUserId]
        );
        if (!$belongs) {
            return false;
        }

        Database::execute(
            'UPDATE players
             SET is_captain = CASE WHEN player_user_id = :player_user_id THEN 1 ELSE 0 END
             WHERE team_id = :team_id',
            ['player_user_id' => $playerUserId, 'team_id' => $teamId]
        );

        return true;
    }
}

