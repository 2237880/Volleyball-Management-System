<?php
declare(strict_types=1);

$role = $_SESSION['user']['role'] ?? null;
?>
<ul class="navbar-nav me-auto mb-2 mb-lg-0">
    <li class="nav-item">
        <a class="nav-link" href="./index.php?r=home">Home</a>
    </li>
    <?php if (isset($_SESSION['user'])): ?>
        <li class="nav-item">
            <a class="nav-link" href="./index.php?r=dashboard">Dashboard</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="./index.php?r=teams">Teams</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="./index.php?r=matches">Matches</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="./index.php?r=match-history">Match History</a>
        </li>
        <?php if (in_array($role ?? '', ['admin', 'coach'], true)): ?>
            <li class="nav-item">
                <a class="nav-link" href="./index.php?r=uploads-player-image">Upload Player Images</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="./index.php?r=uploads-match-report">Upload Match Reports</a>
            </li>
        <?php endif; ?>
        <li class="nav-item">
            <a class="nav-link" href="./index.php?r=logout">Logout</a>
        </li>
    <?php else: ?>
        <li class="nav-item">
            <a class="nav-link" href="./index.php?r=login">Login</a>
        </li>
    <?php endif; ?>
</ul>

<?php if (isset($_SESSION['user'])): ?>
<span class="navbar-text text-white-50">
    Signed in as <?php echo htmlspecialchars($_SESSION['user']['name']); ?>
    <?php if ($role): ?>(<?php echo htmlspecialchars($role); ?>)<?php endif; ?>
</span>
<?php endif; ?>

