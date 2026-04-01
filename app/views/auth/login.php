<?php
declare(strict_types=1);
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="p-4 bg-white rounded shadow-sm">
            <h1 class="h4 mb-3">Login</h1>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars((string)$error); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="./index.php?r=login">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(CSRF::token()); ?>">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input class="form-control" type="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input class="form-control" type="password" name="password" required>
                </div>
                <button class="btn btn-primary w-100" type="submit">Login</button>
            </form>

            <hr>

            <div class="d-flex gap-2 flex-wrap">
                <a class="btn btn-outline-secondary flex-fill" href="./index.php?r=register-player">Register as Player</a>
                <a class="btn btn-outline-secondary flex-fill" href="./index.php?r=register-coach">Register as Coach</a>
                <a class="btn btn-outline-secondary flex-fill" href="./index.php?r=register-admin">Register as Admin</a>
            </div>
        </div>
    </div>
</div>

