<?php

namespace App\Infrastructure\Logging;

class FileLogger
{
    private string $filePath;

    public function __construct(?string $filePath = null)
    {
        if ($filePath === null) {
            // __DIR__ = project-root/src/Infrastructure/Logging
            $this->filePath = __DIR__ . '/../../../storage/logs/app.log';
        } else {
            $this->filePath = $filePath;
        }

        $dir = \dirname($this->filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        if (!file_exists($this->filePath)) {
            // создаём пустой лог-файл
            touch($this->filePath);
        }
    }

    /**
     * @param string $level        Например: "info", "warning", "error"
     * @param string $messageKey   Ключ локализации, не русский текст
     * @param array<string,mixed> $context Дополнительные данные (user_id, poll_id и т.п.)
     */
    public function log(string $level, string $messageKey, array $context = []): void
    {
        $record = [
            'timestamp' => (new \DateTimeImmutable())->format('c'),
            'level'     => $level,
            'message'   => $messageKey,
            'context'   => $context,
        ];

        $line = json_encode($record, JSON_UNESCAPED_SLASHES) . PHP_EOL;

        // простая файловая запись без заморочек
        file_put_contents($this->filePath, $line, FILE_APPEND);
    }

    public function info(string $messageKey, array $context = []): void
    {
        $this->log('info', $messageKey, $context);
    }

    public function warning(string $messageKey, array $context = []): void
    {
        $this->log('warning', $messageKey, $context);
    }

    public function error(string $messageKey, array $context = []): void
    {
        $this->log('error', $messageKey, $context);
    }
}
