<?php
declare(strict_types=1);

session_start();

$BASE_PATH = dirname(__DIR__, 2);
require_once $BASE_PATH . '/app/lib/Database.php';
require_once $BASE_PATH . '/app/lib/Auth.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$u = Auth::user();
$userId = (int)$u['id'];
$role = (string)$u['role'];
$action = (string)($_GET['action'] ?? '');

try {
    if ($action === 'player_stats') {
        if ($role === 'player') {
            $sql = '
                SELECT u.id AS player_user_id,
                       u.name AS player_name,
                       SUM(CASE WHEN m.home_team_id = p.team_id THEN s.home_score ELSE s.away_score END) AS points,
                       SUM(CASE WHEN s.winner_team_id = p.team_id THEN 1 ELSE 0 END) AS wins
                FROM players p
                INNER JOIN users u ON u.id = p.player_user_id
                INNER JOIN matches m ON (m.home_team_id = p.team_id OR m.away_team_id = p.team_id)
                INNER JOIN match_scores s ON s.match_id = m.id
                WHERE p.player_user_id = :player_user_id
                GROUP BY u.id, u.name
                ORDER BY wins DESC, points DESC
            ';
            $rows = Database::fetchAll($sql, ['player_user_id' => $userId]);
        } elseif ($role === 'coach') {
            $sql = '
                SELECT u.id AS player_user_id,
                       u.name AS player_name,
                       SUM(CASE WHEN m.home_team_id = p.team_id THEN s.home_score ELSE s.away_score END) AS points,
                       SUM(CASE WHEN s.winner_team_id = p.team_id THEN 1 ELSE 0 END) AS wins
                FROM players p
                INNER JOIN users u ON u.id = p.player_user_id
                INNER JOIN teams t ON t.id = p.team_id
                INNER JOIN matches m ON (m.home_team_id = p.team_id OR m.away_team_id = p.team_id)
                INNER JOIN match_scores s ON s.match_id = m.id
                WHERE t.coach_user_id = :coach_user_id
                GROUP BY u.id, u.name
                ORDER BY wins DESC, points DESC
            ';
            $rows = Database::fetchAll($sql, ['coach_user_id' => $userId]);
        } else {
            // admin
            $sql = '
                SELECT u.id AS player_user_id,
                       u.name AS player_name,
                       SUM(CASE WHEN m.home_team_id = p.team_id THEN s.home_score ELSE s.away_score END) AS points,
                       SUM(CASE WHEN s.winner_team_id = p.team_id THEN 1 ELSE 0 END) AS wins
                FROM players p
                INNER JOIN users u ON u.id = p.player_user_id
                INNER JOIN matches m ON (m.home_team_id = p.team_id OR m.away_team_id = p.team_id)
                INNER JOIN match_scores s ON s.match_id = m.id
                GROUP BY u.id, u.name
                ORDER BY wins DESC, points DESC
            ';
            $rows = Database::fetchAll($sql);
        }

        echo json_encode(['player_stats' => array_map(function ($r) {
            return [
                'player_user_id' => (int)$r['player_user_id'],
                'player_name' => (string)$r['player_name'],
                'wins' => (int)$r['wins'],
                'points' => (int)$r['points'],
            ];
        }, $rows)]);
        exit;
    }

    if ($action === 'team_ranking') {
        if ($role === 'coach') {
            $sql = '
                SELECT t.id AS team_id,
                       t.name AS team_name,
                       SUM(CASE WHEN s.winner_team_id = t.id THEN 1 ELSE 0 END) AS wins,
                       SUM(CASE WHEN m.home_team_id = t.id THEN s.home_score ELSE s.away_score END) AS points
                FROM teams t
                INNER JOIN matches m ON (m.home_team_id = t.id OR m.away_team_id = t.id)
                INNER JOIN match_scores s ON s.match_id = m.id
                WHERE t.coach_user_id = :coach_user_id
                GROUP BY t.id, t.name
                ORDER BY wins DESC, points DESC
            ';
            $rows = Database::fetchAll($sql, ['coach_user_id' => $userId]);
        } else {
            $sql = '
                SELECT t.id AS team_id,
                       t.name AS team_name,
                       SUM(CASE WHEN s.winner_team_id = t.id THEN 1 ELSE 0 END) AS wins,
                       SUM(CASE WHEN m.home_team_id = t.id THEN s.home_score ELSE s.away_score END) AS points
                FROM teams t
                INNER JOIN matches m ON (m.home_team_id = t.id OR m.away_team_id = t.id)
                INNER JOIN match_scores s ON s.match_id = m.id
                GROUP BY t.id, t.name
                ORDER BY wins DESC, points DESC
            ';
            $rows = Database::fetchAll($sql);
        }

        echo json_encode(['team_ranking' => array_map(function ($r) {
            return [
                'team_id' => (int)$r['team_id'],
                'team_name' => (string)$r['team_name'],
                'wins' => (int)$r['wins'],
                'points' => (int)$r['points'],
            ];
        }, $rows)]);
        exit;
    }

    if ($action === 'leaderboard') {
        // Top players by points (tie-break by wins).
        $sql = '
            SELECT u.id AS player_user_id,
                   u.name AS player_name,
                   SUM(CASE WHEN m.home_team_id = p.team_id THEN s.home_score ELSE s.away_score END) AS points,
                   SUM(CASE WHEN s.winner_team_id = p.team_id THEN 1 ELSE 0 END) AS wins
            FROM players p
            INNER JOIN users u ON u.id = p.player_user_id
            INNER JOIN matches m ON (m.home_team_id = p.team_id OR m.away_team_id = p.team_id)
            INNER JOIN match_scores s ON s.match_id = m.id
        ';

        $params = [];
        if ($role === 'player') {
            $sql .= ' WHERE p.player_user_id = :player_user_id ';
            $params['player_user_id'] = $userId;
        } elseif ($role === 'coach') {
            $sql = '
                SELECT u.id AS player_user_id,
                       u.name AS player_name,
                       SUM(CASE WHEN m.home_team_id = p.team_id THEN s.home_score ELSE s.away_score END) AS points,
                       SUM(CASE WHEN s.winner_team_id = p.team_id THEN 1 ELSE 0 END) AS wins
                FROM players p
                INNER JOIN users u ON u.id = p.player_user_id
                INNER JOIN teams t ON t.id = p.team_id
                INNER JOIN matches m ON (m.home_team_id = p.team_id OR m.away_team_id = p.team_id)
                INNER JOIN match_scores s ON s.match_id = m.id
                WHERE t.coach_user_id = :coach_user_id
            ';
            $params['coach_user_id'] = $userId;
        }

        $sql .= '
            GROUP BY u.id, u.name
            ORDER BY points DESC, wins DESC
            LIMIT 10
        ';

        $rows = Database::fetchAll($sql, $params);
        echo json_encode(['leaderboard' => array_map(function ($r) {
            return [
                'player_user_id' => (int)$r['player_user_id'],
                'player_name' => (string)$r['player_name'],
                'wins' => (int)$r['wins'],
                'points' => (int)$r['points'],
            ];
        }, $rows)]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['error' => 'Unknown action']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}

