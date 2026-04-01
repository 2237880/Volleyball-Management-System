<?php
declare(strict_types=1);
?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="p-3 bg-white rounded shadow-sm h-100">
            <h2 class="h5 mb-2"><?php echo htmlspecialchars((string)$team['name']); ?></h2>
            <div class="text-muted mb-3">
                Coach: <?php echo htmlspecialchars((string)($team['coach_name'] ?? '')); ?>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-warning">
                    <?php echo htmlspecialchars((string)$error); ?>
                </div>
            <?php endif; ?>

            <?php if (in_array($role ?? '', ['admin', 'coach'], true)): ?>
                <div class="d-flex gap-2 flex-wrap">
                    <a class="btn btn-outline-secondary btn-sm" href="./index.php?r=teams-edit&id=<?php echo (int)$team['id']; ?>">Edit</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="p-3 bg-white rounded shadow-sm">
            <h3 class="h5 mb-3">Roster</h3>

            <?php if (empty($roster)): ?>
                <div class="alert alert-info">No players assigned yet.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Player</th>
                                <th>Position</th>
                                <th>Captain</th>
                                <?php if (in_array($role ?? '', ['admin', 'coach'], true)): ?>
                                    <th style="width: 220px;">Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($roster as $p): ?>
                            <tr>
                                <td><?php echo htmlspecialchars((string)$p['player_name']); ?></td>
                                <td><?php echo htmlspecialchars((string)$p['position']); ?></td>
                                <td>
                                    <?php if ((int)$p['is_captain'] === 1): ?>
                                        <span class="badge text-bg-success">Captain</span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <?php if (in_array($role ?? '', ['admin', 'coach'], true)): ?>
                                    <td>
                                        <?php if ((int)$p['is_captain'] !== 1): ?>
                <form method="post" action="./index.php?r=teams-view&id=<?php echo (int)$team['id']; ?>" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(CSRF::token()); ?>">
                                                <input type="hidden" name="action" value="set_captain">
                                                <input type="hidden" name="player_user_id" value="<?php echo (int)$p['player_user_id']; ?>">
                                                <button class="btn btn-sm btn-outline-primary" type="submit">Set Captain</button>
                                            </form>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-secondary" type="button" disabled>Current Captain</button>
                                        <?php endif; ?>

                                        <form method="post" action="./index.php?r=teams-view&id=<?php echo (int)$team['id']; ?>" class="d-inline ms-2">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(CSRF::token()); ?>">
                                            <input type="hidden" name="action" value="remove_player">
                                            <input type="hidden" name="player_user_id" value="<?php echo (int)$p['player_user_id']; ?>">
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Remove</button>
                                        </form>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <hr>

            <?php if (in_array($role ?? '', ['admin', 'coach'], true)): ?>
                <h4 class="h6 mb-3">Add Player</h4>
                <form method="post" action="./index.php?r=teams-view&id=<?php echo (int)$team['id']; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(CSRF::token()); ?>">
                    <input type="hidden" name="action" value="add_player">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-7">
                            <label class="form-label">Player</label>
                            <select class="form-select" name="player_user_id" required>
                                <option value="">Select player...</option>
                                <?php foreach ($availablePlayers as $pl): ?>
                                    <option value="<?php echo (int)$pl['id']; ?>">
                                        <?php echo htmlspecialchars($pl['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Position</label>
                            <input class="form-control" type="text" name="position" placeholder="e.g., Setter" required>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary" type="submit">Add to Team</button>
                        </div>
                    </div>
                </form>
            <?php else: ?>
                <div class="alert alert-secondary mt-3">
                    Only Admin/Coach can manage the roster.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

