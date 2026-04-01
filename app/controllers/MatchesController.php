<?php
declare(strict_types=1);

final class MatchesController
{
    public function index(): void
    {
        $u = Auth::requireUser();
        $matches = MatchModel::getUpcomingMatchesForUser((int)$u['id'], (string)$u['role']);

        View::render('matches/index', [
            'title' => 'Upcoming Matches',
            'matches' => $matches,
            'role' => $u['role'],
        ]);
    }

    public function create(): void
    {
        $u = Auth::requireUser();
        RBAC::requireRole(['admin', 'coach']);

        $accessibleTeams = TeamModel::getTeamsForUser((int)$u['id'], (string)$u['role']);
        if (count($accessibleTeams) < 2) {
            View::render('matches/create', [
                'title' => 'Create Match Fixture',
                'error' => 'You need at least two teams to schedule a match.',
                'teams' => $accessibleTeams,
            ]);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::validate()) {
                Flash::set('error', 'Invalid CSRF token.');
                View::render('matches/create', [
                    'title' => 'Create Match Fixture',
                    'error' => 'Invalid CSRF token.',
                    'teams' => $accessibleTeams,
                ]);
                return;
            }

            $homeTeamId = (int)($_POST['home_team_id'] ?? 0);
            $awayTeamId = (int)($_POST['away_team_id'] ?? 0);
            $scheduledAtRaw = (string)($_POST['scheduled_at'] ?? '');

            if ($homeTeamId <= 0 || $awayTeamId <= 0 || $scheduledAtRaw === '') {
                Flash::set('error', 'All match fields are required.');
                View::render('matches/create', [
                    'title' => 'Create Match Fixture',
                    'error' => 'All match fields are required.',
                    'teams' => $accessibleTeams,
                ]);
                return;
            }

            if ($homeTeamId === $awayTeamId) {
                Flash::set('error', 'Home team and away team must be different.');
                View::render('matches/create', [
                    'title' => 'Create Match Fixture',
                    'error' => 'Home team and away team must be different.',
                    'teams' => $accessibleTeams,
                ]);
                return;
            }

            // datetime-local returns: YYYY-MM-DDTHH:MM
            $scheduledAt = str_replace('T', ' ', $scheduledAtRaw) . ':00';

            $matchId = MatchModel::createMatch($homeTeamId, $awayTeamId, $scheduledAt, (int)$u['id']);
            Flash::set('success', 'Match fixture created.');
            header('Location: ./index.php?r=matches');
            exit;
        }

        View::render('matches/create', [
            'title' => 'Create Match Fixture',
            'error' => null,
            'teams' => $accessibleTeams,
        ]);
    }
}

