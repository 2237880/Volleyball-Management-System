<?php
declare(strict_types=1);

final class View
{
    public static function render(string $view, array $data = []): void
    {
        $BASE_PATH = dirname(__DIR__, 2);

        extract($data, EXTR_SKIP);

        ob_start();
        $viewFile = $BASE_PATH . '/app/views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            http_response_code(500);
            echo 'View not found: ' . htmlspecialchars($view);
            return;
        }
        require $viewFile;
        $content = ob_get_clean();

        require $BASE_PATH . '/app/views/layout.php';
    }
}

