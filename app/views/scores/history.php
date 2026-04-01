<?php
declare(strict_types=1);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0"><?php echo htmlspecialchars($title ?? 'Match History'); ?></h1>
</div>

<?php if (empty($matches)): ?>
    <div class="alert alert-info">No match results entered yet.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Scheduled</th>
                    <th>Fixture</th>
                    <th>Score</th>
                    <th>Winner</th>
                    <?php if (in_array($role ?? '', ['admin', 'coach'], true)): ?>
                        <th style="width: 220px;">Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($matches as $m): ?>
                <tr>
                    <td><?php echo (int)$m['id']; ?></td>
                    <td><?php echo htmlspecialchars((string)$m['scheduled_at']); ?></td>
                    <td>
                        <?php echo htmlspecialchars((string)$m['home_team_name']); ?>
                        <span class="text-muted">vs</span>
                        <?php echo htmlspecialchars((string)$m['away_team_name']); ?>
                    </td>
                    <td>
                        <?php echo (int)$m['home_score']; ?>
                        <span class="text-muted">-</span>
                        <?php echo (int)$m['away_score']; ?>
                    </td>
                    <td><?php echo htmlspecialchars((string)$m['winner_label']); ?></td>
                    <?php if (in_array($role ?? '', ['admin', 'coach'], true)): ?>
                        <td>
                            <a class="btn btn-outline-primary btn-sm"
                               href="./index.php?r=score-edit&id=<?php echo (int)$m['id']; ?>">
                                Enter/Update Scores
                            </a>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

