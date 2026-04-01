<?php
declare(strict_types=1);

final class Flash
{
    private const SESSION_KEY = 'flash';

    public static function set(string $type, string $message): void
    {
        if (!isset($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }
        $_SESSION[self::SESSION_KEY][$type] = $message;
    }

    public static function pull(string $type): ?string
    {
        if (!isset($_SESSION[self::SESSION_KEY][$type])) {
            return null;
        }
        $msg = (string)$_SESSION[self::SESSION_KEY][$type];
        unset($_SESSION[self::SESSION_KEY][$type]);
        return $msg;
    }
}

