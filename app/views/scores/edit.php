<?php
declare(strict_types=1);

$homeScoreVal = $match['home_score'] ?? '';
$awayScoreVal = $match['away_score'] ?? '';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="p-4 bg-white rounded shadow-sm">
            <h1 class="h4 mb-3"><?php echo htmlspecialchars((string)($title ?? 'Enter/Update Scores')); ?></h1>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars((string)$error); ?></div>
            <?php endif; ?>

            <div class="mb-3 text-muted">
                Match ID: <?php echo (int)$match['id']; ?> |
                Scheduled: <?php echo htmlspecialchars((string)$match['scheduled_at']); ?>
            </div>

            <div class="border rounded p-3 mb-4">
                <div class="fw-semibold">
                    <?php echo htmlspecialchars((string)$match['home_team_name']); ?>
                    <span class="text-muted">vs</span>
                    <?php echo htmlspecialchars((string)$match['away_team_name']); ?>
                </div>
                <?php if (isset($match['winner_team_id'])): ?>
                    <div class="mt-2">
                        Current Winner:
                        <?php
                        $winner = 'Draw';
                        if ($match['winner_team_id'] !== null) {
                            if ((int)$match['winner_team_id'] === (int)$match['home_team_id']) {
                                $winner = $match['home_team_name'] . ' won';
                            } elseif ((int)$match['winner_team_id'] === (int)$match['away_team_id']) {
                                $winner = $match['away_team_name'] . ' won';
                            }
                        }
                        echo htmlspecialchars((string)$winner);
                        ?>
                    </div>
                <?php endif; ?>
            </div>

            <form method="post" action="./index.php?r=score-edit&id=<?php echo (int)$match['id']; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(CSRF::token()); ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Home Score</label>
                        <input class="form-control" type="number" min="0" name="home_score"
                               value="<?php echo htmlspecialchars((string)$homeScoreVal); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Away Score</label>
                        <input class="form-control" type="number" min="0" name="away_score"
                               value="<?php echo htmlspecialchars((string)$awayScoreVal); ?>" required>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary w-100" type="submit">Save Result</button>
                    </div>
                </div>
            </form>

            <div class="mt-3">
                <a class="btn btn-outline-secondary btn-sm" href="./index.php?r=match-history">Back to History</a>
            </div>
        </div>
    </div>
</div>

