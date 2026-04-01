<?php
declare(strict_types=1);

// Update these values to match your local XAMPP MySQL setup.
// You can also set them via environment variables.

return [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'name' => getenv('DB_NAME') ?: 'volleyball',
    'user' => getenv('DB_USER') ?: 'root',
    'pass' => getenv('DB_PASS') ?: '',
];

