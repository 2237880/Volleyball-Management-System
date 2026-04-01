<?php
declare(strict_types=1);

final class ScoreModel
{
    public static function getScoredMatchesForUser(int $userId, string $role): array
    {
        $baseSql = '
            SELECT m.id,
                   m.scheduled_at,
                   ht.name AS home_team_name,
                   at.name AS away_team_name,
                   s.home_score,
                   s.away_score,
                   s.winner_team_id,
                   m.home_team_id,
                   m.away_team_id
            FROM matches m
            INNER JOIN match_scores s ON s.match_id = m.id
            INNER JOIN teams ht ON ht.id = m.home_team_id
            INNER JOIN teams at ON at.id = m.away_team_id
            WHERE 1=1
        ';

        if ($role === 'admin') {
            $sql = $baseSql . ' ORDER BY m.scheduled_at DESC';
            return Database::fetchAll($sql);
        }

        if ($role === 'coach') {
            $sql = $baseSql . '
                AND (m.home_team_id IN (SELECT id FROM teams WHERE coach_user_id = :coach_id_home)
                     OR m.away_team_id IN (SELECT id FROM teams WHERE coach_user_id = :coach_id_away))
                ORDER BY m.scheduled_at DESC
            ';
            return Database::fetchAll($sql, [
                'coach_id_home' => $userId,
                'coach_id_away' => $userId,
            ]);
        }

        // Player
        $sql = $baseSql . '
            AND (m.home_team_id IN (SELECT team_id FROM players WHERE player_user_id = :p_uid_home)
                 OR m.away_team_id IN (SELECT team_id FROM players WHERE player_user_id = :p_uid_away))
            ORDER BY m.scheduled_at DESC
        ';
        return Database::fetchAll($sql, [
            'p_uid_home' => $userId,
            'p_uid_away' => $userId,
        ]);
    }

    public static function getMatchForScoreEdit(int $matchId): ?array
    {
        $sql = '
            SELECT m.*,
                   ht.name AS home_team_name,
                   at.name AS away_team_name,
                   s.home_score,
                   s.away_score,
                   s.winner_team_id
            FROM matches m
            INNER JOIN teams ht ON ht.id = m.home_team_id
            INNER JOIN teams at ON at.id = m.away_team_id
            LEFT JOIN match_scores s ON s.match_id = m.id
            WHERE m.id = :match_id
            LIMIT 1
        ';

        return Database::fetchOne($sql, ['match_id' => $matchId]);
    }

    public static function upsertScores(int $matchId, int $homeScore, int $awayScore, int $enteredBy): void
    {
        $match = Database::fetchOne(
            'SELECT home_team_id, away_team_id FROM matches WHERE id = :match_id LIMIT 1',
            ['match_id' => $matchId]
        );
        if (!$match) {
            throw new RuntimeException('Match not found');
        }

        $homeTeamId = (int)$match['home_team_id'];
        $awayTeamId = (int)$match['away_team_id'];

        $winnerTeamId = null;
        if ($homeScore > $awayScore) {
            $winnerTeamId = $homeTeamId;
        } elseif ($awayScore > $homeScore) {
            $winnerTeamId = $awayTeamId;
        }

        $existing = Database::fetchOne(
            'SELECT match_id FROM match_scores WHERE match_id = :match_id LIMIT 1',
            ['match_id' => $matchId]
        );

        if ($existing) {
            Database::execute(
                'UPDATE match_scores
                 SET home_score = :home_score,
                     away_score = :away_score,
                     winner_team_id = :winner_team_id,
                     entered_by = :entered_by,
                     updated_at = NOW()
                 WHERE match_id = :match_id',
                [
                    'home_score' => $homeScore,
                    'away_score' => $awayScore,
                    'winner_team_id' => $winnerTeamId,
                    'entered_by' => $enteredBy,
                    'match_id' => $matchId,
                ]
            );
            return;
        }

        Database::execute(
            'INSERT INTO match_scores (match_id, home_score, away_score, winner_team_id, entered_by)
             VALUES (:match_id, :home_score, :away_score, :winner_team_id, :entered_by)',
            [
                'match_id' => $matchId,
                'home_score' => $homeScore,
                'away_score' => $awayScore,
                'winner_team_id' => $winnerTeamId,
                'entered_by' => $enteredBy,
            ]
        );
    }
}

