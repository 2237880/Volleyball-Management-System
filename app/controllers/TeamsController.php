<?php
declare(strict_types=1);

final class TeamsController
{
    public function index(): void
    {
        $u = Auth::requireUser();
        $teams = TeamModel::getTeamsForUser((int)$u['id'], (string)$u['role']);
        View::render('teams/index', [
            'title' => 'Teams',
            'teams' => $teams,
            'role' => $u['role'],
        ]);
    }

    public function create(): void
    {
        $u = Auth::requireUser();
        RBAC::requireRole(['admin', 'coach']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::validate()) {
                Flash::set('error', 'Invalid CSRF token.');
                View::render('teams/create', [
                    'title' => 'Create Team',
                    'error' => 'Invalid CSRF token.',
                    'role' => $u['role'],
                    'coaches' => TeamModel::getCoaches(),
                ]);
                return;
            }

            $name = trim((string)($_POST['name'] ?? ''));
            if ($name === '') {
                Flash::set('error', 'Team name is required.');
                View::render('teams/create', [
                    'title' => 'Create Team',
                    'error' => 'Team name is required.',
                    'role' => $u['role'],
                    'coaches' => TeamModel::getCoaches(),
                ]);
                return;
            }

            $coachUserId = (int)$u['id'];
            if ($u['role'] === 'admin') {
                $coachUserId = (int)($_POST['coach_user_id'] ?? 0);
                if ($coachUserId <= 0) {
                    Flash::set('error', 'Coach is required.');
                    View::render('teams/create', [
                        'title' => 'Create Team',
                        'error' => 'Coach is required.',
                        'role' => $u['role'],
                        'coaches' => TeamModel::getCoaches(),
                    ]);
                    return;
                }
            }

            try {
                $teamId = TeamModel::createTeam($name, $coachUserId);
            } catch (Throwable $e) {
                Flash::set('error', 'Could not create team. Ensure the name is unique.');
                View::render('teams/create', [
                    'title' => 'Create Team',
                    'error' => 'Could not create team. Ensure the name is unique.',
                    'role' => $u['role'],
                    'coaches' => TeamModel::getCoaches(),
                ]);
                return;
            }

            Flash::set('success', 'Team created successfully.');
            header('Location: ./index.php?r=teams-view&id=' . $teamId);
            exit;
        }

        View::render('teams/create', [
            'title' => 'Create Team',
            'error' => null,
            'role' => $u['role'],
            'coaches' => TeamModel::getCoaches(),
        ]);
    }

    public function edit(int $teamId): void
    {
        $u = Auth::requireUser();
        RBAC::requireRole(['admin', 'coach']);

        if (!TeamModel::isTeamAccessibleToUser($teamId, (int)$u['id'], (string)$u['role'])) {
            http_response_code(403);
            exit('Forbidden');
        }

        $team = TeamModel::getTeamById($teamId);
        if (!$team) {
            http_response_code(404);
            View::render('home', ['title' => 'Team not found']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::validate()) {
                Flash::set('error', 'Invalid CSRF token.');
                View::render('teams/edit', [
                    'title' => 'Edit Team',
                    'error' => 'Invalid CSRF token.',
                    'team' => $team,
                    'role' => $u['role'],
                    'coaches' => TeamModel::getCoaches(),
                ]);
                return;
            }

            $name = trim((string)($_POST['name'] ?? ''));
            $coachUserId = $team['coach_user_id'];

            if ($name === '') {
                Flash::set('error', 'Team name is required.');
                View::render('teams/edit', [
                    'title' => 'Edit Team',
                    'error' => 'Team name is required.',
                    'team' => $team,
                    'role' => $u['role'],
                    'coaches' => TeamModel::getCoaches(),
                ]);
                return;
            }

            if ($u['role'] === 'admin') {
                $coachUserId = (int)($_POST['coach_user_id'] ?? 0);
                if ($coachUserId <= 0) {
                    Flash::set('error', 'Coach is required.');
                    View::render('teams/edit', [
                        'title' => 'Edit Team',
                        'error' => 'Coach is required.',
                        'team' => $team,
                        'role' => $u['role'],
                        'coaches' => TeamModel::getCoaches(),
                    ]);
                    return;
                }
            }

            TeamModel::updateTeam($teamId, $name, $coachUserId);
            Flash::set('success', 'Team updated successfully.');
            header('Location: ./index.php?r=teams-view&id=' . $teamId);
            exit;
        }

        View::render('teams/edit', [
            'title' => 'Edit Team',
            'error' => null,
            'team' => $team,
            'role' => $u['role'],
            'coaches' => TeamModel::getCoaches(),
        ]);
    }

    public function view(int $teamId): void
    {
        $u = Auth::requireUser();
        $role = (string)$u['role'];

        if (!TeamModel::isTeamAccessibleToUser($teamId, (int)$u['id'], $role)) {
            http_response_code(403);
            exit('Forbidden');
        }

        $team = TeamModel::getTeamById($teamId);
        if (!$team) {
            http_response_code(404);
            View::render('home', ['title' => 'Team not found']);
            return;
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::validate()) {
                Flash::set('error', 'Invalid CSRF token.');
                $error = 'Invalid CSRF token.';
            } else {
            $action = (string)($_POST['action'] ?? '');

            // Only admin/coach can mutate roster.
            if (!in_array($role, ['admin', 'coach'], true)) {
                http_response_code(403);
                exit('Forbidden');
            }

            if ($action === 'add_player') {
                $playerUserId = (int)($_POST['player_user_id'] ?? 0);
                $position = trim((string)($_POST['position'] ?? 'Unknown'));
                if ($playerUserId <= 0 || $position === '') {
                    $error = 'Player and position are required.';
                } else {
                    $ok = TeamModel::addPlayer($teamId, $playerUserId, $position);
                    if (!$ok) {
                        $error = 'That player is already on the team.';
                    }
                }
            } elseif ($action === 'remove_player') {
                $playerUserId = (int)($_POST['player_user_id'] ?? 0);
                if ($playerUserId > 0) {
                    TeamModel::removePlayer($teamId, $playerUserId);
                }
            } elseif ($action === 'set_captain') {
                $playerUserId = (int)($_POST['player_user_id'] ?? 0);
                if ($playerUserId > 0) {
                    $ok = TeamModel::setCaptain($teamId, $playerUserId);
                    if (!$ok) {
                        $error = 'Invalid captain selection.';
                    }
                }
            }
            }

            if ($error) {
                Flash::set('error', $error);
            } else {
                Flash::set('success', 'Team updated.');
            }
        }

        $roster = TeamModel::getRoster($teamId);
        $availablePlayers = TeamModel::getPlayersNotInTeam($teamId);
        View::render('teams/view', [
            'title' => 'Team: ' . $team['name'],
            'team' => $team,
            'role' => $role,
            'error' => $error,
            'roster' => $roster,
            'availablePlayers' => $availablePlayers,
        ]);
    }
}

