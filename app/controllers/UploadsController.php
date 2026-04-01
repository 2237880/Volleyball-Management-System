<?php
declare(strict_types=1);

final class UploadsController
{
    private function handleUpload(string $fileField, string $targetDir, array $allowedExts, int $maxBytes): array
    {
        if (!isset($_FILES[$fileField])) {
            return ['ok' => false, 'error' => 'No file provided.'];
        }

        $f = $_FILES[$fileField];
        if (!isset($f['error']) || $f['error'] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => 'Upload failed.'];
        }
        if (!isset($f['tmp_name']) || !is_uploaded_file($f['tmp_name'])) {
            return ['ok' => false, 'error' => 'Invalid upload payload.'];
        }
        if (!isset($f['size']) || (int)$f['size'] > $maxBytes) {
            return ['ok' => false, 'error' => 'File is too large.'];
        }

        $originalName = (string)($f['name'] ?? '');
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExts, true)) {
            return ['ok' => false, 'error' => 'Invalid file extension.'];
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($f['tmp_name']);
        if ($ext === 'jpeg') {
            $ext = 'jpg';
        }
        if ($ext === 'jpg' && ($mime !== 'image/jpeg' && $mime !== 'image/jpg')) {
            return ['ok' => false, 'error' => 'Invalid MIME type.'];
        }
        if ($ext === 'png' && $mime !== 'image/png') {
            return ['ok' => false, 'error' => 'Invalid MIME type.'];
        }
        if ($ext === 'gif' && $mime !== 'image/gif') {
            return ['ok' => false, 'error' => 'Invalid MIME type.'];
        }
        if ($ext === 'pdf' && $mime !== 'application/pdf') {
            return ['ok' => false, 'error' => 'Invalid MIME type.'];
        }

        if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true)) {
            return ['ok' => false, 'error' => 'Upload directory is not writable.'];
        }

        $newName = bin2hex(random_bytes(16)) . '.' . $ext;
        $targetPath = rtrim($targetDir, '/\\') . DIRECTORY_SEPARATOR . $newName;

        if (!move_uploaded_file($f['tmp_name'], $targetPath)) {
            return ['ok' => false, 'error' => 'Failed to store file.'];
        }

        return ['ok' => true, 'filename' => $newName];
    }

    public function playerImage(): void
    {
        $u = Auth::requireUser();
        RBAC::requireRole(['admin', 'coach']);

        $players = Database::fetchAll(
            'SELECT id, name, email FROM users WHERE role = :role ORDER BY name',
            ['role' => 'player']
        );

        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::validate()) {
                Flash::set('error', 'Invalid CSRF token.');
                $error = 'Invalid CSRF token.';
            } else {
            $playerUserId = (int)($_POST['player_user_id'] ?? 0);
            if ($playerUserId <= 0) {
                $error = 'Please select a player.';
            } else {
                $res = $this->handleUpload(
                    'file',
                    dirname(__DIR__, 3) . '/public/uploads/players',
                    ['jpg', 'jpeg', 'png', 'gif'],
                    2 * 1024 * 1024
                );
                if (!$res['ok']) {
                    $error = $res['error'] ?? 'Upload failed.';
                } else {
                    Flash::set('success', 'Player image uploaded successfully.');
                    header('Location: ./index.php?r=uploads-player-image');
                    exit;
                }
            }
            }
        }

        View::render('uploads/player_image', [
            'title' => 'Upload Player Image',
            'players' => $players,
            'error' => $error,
            'success' => null,
        ]);
    }

    public function matchReport(): void
    {
        $u = Auth::requireUser();
        RBAC::requireRole(['admin', 'coach']);

        $matches = Database::fetchAll(
            'SELECT m.id,
                    m.scheduled_at,
                    ht.name AS home_team_name,
                    at.name AS away_team_name
             FROM matches m
             INNER JOIN teams ht ON ht.id = m.home_team_id
             INNER JOIN teams at ON at.id = m.away_team_id
             ORDER BY m.scheduled_at DESC
             LIMIT 100'
        );

        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::validate()) {
                Flash::set('error', 'Invalid CSRF token.');
                $error = 'Invalid CSRF token.';
            } else {
            $matchId = (int)($_POST['match_id'] ?? 0);
            if ($matchId <= 0) {
                $error = 'Please select a match.';
            } else {
                $res = $this->handleUpload(
                    'file',
                    dirname(__DIR__, 3) . '/public/uploads/match_reports',
                    ['jpg', 'jpeg', 'png', 'gif', 'pdf'],
                    5 * 1024 * 1024
                );
                if (!$res['ok']) {
                    $error = $res['error'] ?? 'Upload failed.';
                } else {
                    Flash::set('success', 'Match report uploaded successfully.');
                    header('Location: ./index.php?r=uploads-match-report');
                    exit;
                }
            }
            }
        }

        View::render('uploads/match_report', [
            'title' => 'Upload Match Report',
            'matches' => $matches,
            'error' => $error,
            'success' => null,
        ]);
    }
}

