<?php
declare(strict_types=1);
?>

<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="p-4 bg-white rounded shadow-sm">
            <h1 class="h4 mb-3">Edit Team</h1>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars((string)$error); ?></div>
            <?php endif; ?>

            <form method="post" action="./index.php?r=teams-edit&id=<?php echo (int)$team['id']; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(CSRF::token()); ?>">
                <div class="mb-3">
                    <label class="form-label">Team Name</label>
                    <input class="form-control" type="text" name="name" value="<?php echo htmlspecialchars((string)$team['name']); ?>" required>
                </div>

                <?php if (($role ?? '') === 'admin'): ?>
                    <div class="mb-3">
                        <label class="form-label">Coach</label>
                        <select class="form-select" name="coach_user_id" required>
                            <option value="">Select coach...</option>
                            <?php foreach ($coaches as $c): ?>
                                <option value="<?php echo (int)$c['id']; ?>" <?php echo ((int)$team['coach_user_id'] === (int)$c['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php else: ?>
                    <div class="alert alert-secondary">
                        Coaches can rename the team, but coach assignment is managed by Admin.
                    </div>
                <?php endif; ?>

                <button class="btn btn-success w-100" type="submit">Save Changes</button>
            </form>
        </div>
    </div>
</div>

