<?php
declare(strict_types=1);
?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="p-4 bg-white rounded shadow-sm">
            <h1 class="h4 mb-3"><?php echo htmlspecialchars($title ?? 'Upload Player Image'); ?></h1>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars((string)$error); ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars((string)$success); ?></div>
            <?php endif; ?>

            <form method="post" action="./index.php?r=uploads-player-image" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(CSRF::token()); ?>">
                <div class="mb-3">
                    <label class="form-label">Player</label>
                    <select class="form-select" name="player_user_id" required>
                        <option value="">Select player...</option>
                        <?php foreach ($players as $p): ?>
                            <option value="<?php echo (int)$p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Image File (JPG/PNG/GIF)</label>
                    <input class="form-control" type="file" name="file" accept=".jpg,.jpeg,.png,.gif" required>
                </div>

                <button class="btn btn-primary w-100" type="submit">Upload</button>
            </form>
        </div>
    </div>
</div>

