<?php
declare(strict_types=1);

final class CSRF
{
    private const SESSION_KEY = 'csrf_token';

    public static function token(): string
    {
        if (!isset($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }
        return (string)$_SESSION[self::SESSION_KEY];
    }

    public static function validate(): bool
    {
        $posted = (string)($_POST['csrf_token'] ?? '');
        $expected = (string)($_SESSION[self::SESSION_KEY] ?? '');
        if ($posted === '' || $expected === '') {
            return false;
        }
        return hash_equals($expected, $posted);
    }
}

