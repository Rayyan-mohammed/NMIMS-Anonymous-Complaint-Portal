<?php

namespace App\Support;

class ErrorHandler
{
    public static function register(): void
    {
        set_exception_handler([self::class, 'handleException']);
        set_error_handler([self::class, 'handleError']);
    }

    public static function handleException(\Throwable $exception): void
    {
        Logger::error('Unhandled exception', [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);

        http_response_code(500);

        $errorPage = APP_BASE_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . '500.php';
        if (is_file($errorPage)) {
            require $errorPage;
            return;
        }

        echo 'Something went wrong. Please try again later.';
    }

    public static function handleError(int $severity, string $message, string $file, int $line): bool
    {
        Logger::warning('PHP runtime warning/error', [
            'severity' => $severity,
            'message' => $message,
            'file' => $file,
            'line' => $line,
        ]);

        return false;
    }
}
