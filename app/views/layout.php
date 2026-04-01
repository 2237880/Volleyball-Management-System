<?php
declare(strict_types=1);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($title ?? 'Volleyball Management System'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="./index.php?r=home">VolleyBall</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="nav">
            <?php require __DIR__ . '/partials/navbar.php'; ?>
        </div>
    </div>
</nav>

<main class="container py-4">
    <?php if ($msg = Flash::pull('success')): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($msg); ?>
        </div>
    <?php endif; ?>
    <?php if ($msg = Flash::pull('error')): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($msg); ?>
        </div>
    <?php endif; ?>
    <?php echo $content; ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

