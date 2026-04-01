<?php
declare(strict_types=1);

final class MatchModel
{
    public static function getUpcomingMatchesForUser(int $userId, string $role): array
    {
        $baseSql = '
            SELECT m.id,
                   m.scheduled_at,
                   ht.name AS home_team_name,
                   at.name AS away_team_name
            FROM matches m
            INNER JOIN teams ht ON ht.id = m.home_team_id
            INNER JOIN teams at ON at.id = m.away_team_id
            WHERE m.scheduled_at >= NOW()
        ';

        if ($role === 'admin') {
            $sql = $baseSql . ' ORDER BY m.scheduled_at ASC';
            return Database::fetchAll($sql);
        }

        if ($role === 'coach') {
            // Unique placeholder names (PDO native prepares do not allow repeating :name).
            $sql = $baseSql . '
                AND (m.home_team_id IN (SELECT id FROM teams WHERE coach_user_id = :coach_id_home)
                     OR m.away_team_id IN (SELECT id FROM teams WHERE coach_user_id = :coach_id_away))
                ORDER BY m.scheduled_at ASC
            ';
            return Database::fetchAll($sql, [
                'coach_id_home' => $userId,
                'coach_id_away' => $userId,
            ]);
        }

        // Player: matches where their team participates
        $sql = $baseSql . '
            AND (m.home_team_id IN (SELECT team_id FROM players WHERE player_user_id = :p_uid_home)
                 OR m.away_team_id IN (SELECT team_id FROM players WHERE player_user_id = :p_uid_away))
            ORDER BY m.scheduled_at ASC
        ';
        return Database::fetchAll($sql, [
            'p_uid_home' => $userId,
            'p_uid_away' => $userId,
        ]);
    }

    public static function createMatch(int $homeTeamId, int $awayTeamId, string $scheduledAt, int $createdBy): int
    {
        Database::execute(
            'INSERT INTO matches (home_team_id, away_team_id, scheduled_at, created_by)
             VALUES (:home_team_id, :away_team_id, :scheduled_at, :created_by)',
            [
                'home_team_id' => $homeTeamId,
                'away_team_id' => $awayTeamId,
                'scheduled_at' => $scheduledAt,
                'created_by' => $createdBy,
            ]
        );

        $row = Database::fetchOne('SELECT LAST_INSERT_ID() AS id');
        return (int)$row['id'];
    }
}

