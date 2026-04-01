<?php
declare(strict_types=1);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0"><?php echo htmlspecialchars($title ?? 'Teams'); ?></h1>
    <?php if (in_array($role ?? '', ['admin', 'coach'], true)): ?>
        <a class="btn btn-primary" href="./index.php?r=teams-create">Create Team</a>
    <?php endif; ?>
</div>

<?php if (empty($teams)): ?>
    <div class="alert alert-info">No teams available yet.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Team Name</th>
                    <th>Coach</th>
                    <th style="width: 220px;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($teams as $t): ?>
                <tr>
                    <td><?php echo htmlspecialchars((string)$t['id']); ?></td>
                    <td><?php echo htmlspecialchars($t['name']); ?></td>
                    <td><?php echo htmlspecialchars($t['coach_name'] ?? ''); ?></td>
                    <td>
                        <a class="btn btn-outline-primary btn-sm" href="./index.php?r=teams-view&id=<?php echo (int)$t['id']; ?>">View</a>
                        <?php if (in_array($role ?? '', ['admin', 'coach'], true)): ?>
                            <a class="btn btn-outline-secondary btn-sm" href="./index.php?r=teams-edit&id=<?php echo (int)$t['id']; ?>">Edit</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

