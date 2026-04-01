<?php
declare(strict_types=1);
?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="p-4 bg-white rounded shadow-sm">
            <h1 class="h4 mb-3">Create Match Fixture</h1>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars((string)$error); ?></div>
            <?php endif; ?>

            <form method="post" action="./index.php?r=matches-create">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(CSRF::token()); ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Home Team</label>
                        <select class="form-select" name="home_team_id" required>
                            <option value="">Select...</option>
                            <?php foreach ($teams as $t): ?>
                                <option value="<?php echo (int)$t['id']; ?>"><?php echo htmlspecialchars($t['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Away Team</label>
                        <select class="form-select" name="away_team_id" required>
                            <option value="">Select...</option>
                            <?php foreach ($teams as $t): ?>
                                <option value="<?php echo (int)$t['id']; ?>"><?php echo htmlspecialchars($t['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Date & Time</label>
                        <input class="form-control" type="datetime-local" name="scheduled_at" required>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary w-100" type="submit">Create Fixture</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

