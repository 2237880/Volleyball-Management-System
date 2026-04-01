<?php
declare(strict_types=1);

final class RBAC
{
    public static function requireRole(array $roles): void
    {
        $u = Auth::user();
        if (!$u || !isset($u['role']) || !in_array($u['role'], $roles, true)) {
            http_response_code(403);
            exit('Forbidden');
        }
    }
}

