<?php
declare(strict_types=1);

final class ScoresController
{
    public function history(): void
    {
        $u = Auth::requireUser();
        $matches = ScoreModel::getScoredMatchesForUser((int)$u['id'], (string)$u['role']);

        // Compute a human winner label for UI.
        foreach ($matches as &$m) {
            $winnerLabel = 'Draw';
            if ($m['winner_team_id'] !== null) {
                if ((int)$m['winner_team_id'] === (int)$m['home_team_id']) {
                    $winnerLabel = (string)$m['home_team_name'] . ' won';
                } elseif ((int)$m['winner_team_id'] === (int)$m['away_team_id']) {
                    $winnerLabel = (string)$m['away_team_name'] . ' won';
                }
            }
            $m['winner_label'] = $winnerLabel;
        }
        unset($m);

        View::render('scores/history', [
            'title' => 'Match History',
            'matches' => $matches,
            'role' => (string)$u['role'],
        ]);
    }

    public function edit(int $matchId): void
    {
        $u = Auth::requireUser();
        $role = (string)$u['role'];
        RBAC::requireRole(['admin', 'coach']);

        if ($role === 'coach') {
            // Coach can only edit matches where their teams participate.
            $allowed = Database::fetchOne(
                'SELECT m.id
                 FROM matches m
                 WHERE m.id = :match_id
                   AND (m.home_team_id IN (SELECT id FROM teams WHERE coach_user_id = :coach_id_home)
                        OR m.away_team_id IN (SELECT id FROM teams WHERE coach_user_id = :coach_id_away))
                 LIMIT 1',
                [
                    'match_id' => $matchId,
                    'coach_id_home' => (int)$u['id'],
                    'coach_id_away' => (int)$u['id'],
                ]
            );
            if (!$allowed) {
                http_response_code(403);
                exit('Forbidden');
            }
        }

        $match = ScoreModel::getMatchForScoreEdit($matchId);
        if (!$match) {
            http_response_code(404);
            exit('Match not found');
        }

        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::validate()) {
                $error = 'Invalid CSRF token.';
                Flash::set('error', $error);
            } else {
            $homeScore = (int)($_POST['home_score'] ?? -1);
            $awayScore = (int)($_POST['away_score'] ?? -1);
            if ($homeScore < 0 || $awayScore < 0) {
                $error = 'Scores must be 0 or greater.';
            } else {
                try {
                    ScoreModel::upsertScores($matchId, $homeScore, $awayScore, (int)$u['id']);
                } catch (Throwable $e) {
                    $error = 'Could not save scores.';
                }

                if ($error === null) {
                    Flash::set('success', 'Scores saved successfully.');
                    header('Location: ./index.php?r=match-history');
                    exit;
                }
            }
            }
        }

        View::render('scores/edit', [
            'title' => 'Enter/Update Scores',
            'error' => $error,
            'match' => $match,
        ]);
    }
}

