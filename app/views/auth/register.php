<?php
declare(strict_types=1);

$roleLabel = match ($role) {
    'admin' => 'Admin',
    'coach' => 'Coach',
    'player' => 'Player',
    default => 'User',
};

$action = './index.php?r=register-' . $role;
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="p-4 bg-white rounded shadow-sm">
            <h1 class="h4 mb-3">Register - <?php echo htmlspecialchars($roleLabel); ?></h1>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars((string)$error); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?php echo htmlspecialchars($action); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(CSRF::token()); ?>">
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input class="form-control" type="text" name="name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input class="form-control" type="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input class="form-control" type="password" name="password" required>
                </div>

                <button class="btn btn-success w-100" type="submit">Create Account</button>
            </form>

            <div class="mt-3">
                <a class="btn btn-link p-0" href="./index.php?r=login">Back to Login</a>
            </div>
        </div>
    </div>
</div>

