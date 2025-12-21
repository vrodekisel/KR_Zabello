<?php

namespace App\Infrastructure\Logging;

class FileLogger
{
    private string $filePath;

    public function __construct(?string $filePath = null)
    {
        if ($filePath === null) {
            $this->filePath = __DIR__ . '/../../../storage/logs/app.log';
        } else {
            $this->filePath = $filePath;
        }

        $dir = \dirname($this->filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        if (!file_exists($this->filePath)) {
            touch($this->filePath);
        }
    }

    /**
     * @param string $level
     * @param string $messageKey
     * @param array<string,mixed>
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
