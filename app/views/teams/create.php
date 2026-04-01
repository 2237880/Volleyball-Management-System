<?php
declare(strict_types=1);
?>

<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="p-4 bg-white rounded shadow-sm">
            <h1 class="h4 mb-3">Create Team</h1>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars((string)$error); ?></div>
            <?php endif; ?>

            <form method="post" action="./index.php?r=teams-create">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(CSRF::token()); ?>">
                <div class="mb-3">
                    <label class="form-label">Team Name</label>
                    <input class="form-control" type="text" name="name" required>
                </div>

                <?php if (($role ?? '') === 'admin'): ?>
                    <div class="mb-3">
                        <label class="form-label">Coach</label>
                        <select class="form-select" name="coach_user_id" required>
                            <option value="">Select coach...</option>
                            <?php foreach ($coaches as $c): ?>
                                <option value="<?php echo (int)$c['id']; ?>">
                                    <?php echo htmlspecialchars($c['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php else: ?>
                    <div class="alert alert-secondary">
                        As a coach, you will be assigned as the team coach.
                    </div>
                <?php endif; ?>

                <button class="btn btn-primary w-100" type="submit">Create</button>
            </form>
        </div>
    </div>
</div>

