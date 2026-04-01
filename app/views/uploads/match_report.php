<?php
declare(strict_types=1);
?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="p-4 bg-white rounded shadow-sm">
            <h1 class="h4 mb-3"><?php echo htmlspecialchars($title ?? 'Upload Match Report'); ?></h1>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars((string)$error); ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars((string)$success); ?></div>
            <?php endif; ?>

            <form method="post" action="./index.php?r=uploads-match-report" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(CSRF::token()); ?>">
                <div class="mb-3">
                    <label class="form-label">Match</label>
                    <select class="form-select" name="match_id" required>
                        <option value="">Select match...</option>
                        <?php foreach ($matches as $m): ?>
                            <option value="<?php echo (int)$m['id']; ?>">
                                #<?php echo (int)$m['id']; ?>: <?php echo htmlspecialchars((string)$m['home_team_name']); ?> vs <?php echo htmlspecialchars((string)$m['away_team_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Report File (PDF/JPG/PNG/GIF)</label>
                    <input class="form-control" type="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.gif" required>
                </div>

                <button class="btn btn-primary w-100" type="submit">Upload</button>
            </form>
        </div>
    </div>
</div>

