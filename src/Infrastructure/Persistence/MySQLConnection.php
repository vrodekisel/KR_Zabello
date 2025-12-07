<?php

namespace App\Infrastructure\Persistence;

use PDO;
use PDOException;

class MySQLConnection
{
    private PDO $pdo;

    /**
     * @param array{
     *     host?: string,
     *     port?: int|string,
     *     dbname?: string,
     *     user?: string,
     *     password?: string,
     *     charset?: string
     * } $config
     */
    public function __construct(array $config)
    {
        $host    = $config['host']    ?? 'mysql';
        $port    = $config['port']    ?? 3306;
        $dbname  = $config['dbname']  ?? 'content_voting';
        $charset = $config['charset'] ?? 'utf8mb4';
        $user    = $config['user']    ?? 'root';
        $password = $config['password'] ?? '';

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $host,
            $port,
            $dbname,
            $charset
        );

        try {
            $this->pdo = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            // ВРЕМЕННО: выводим подробности, чтобы понять, что именно не так.
            // Оно уйдёт в docker logs app.
            error_log('DB connection failed. DSN=' . $dsn . ' Error=' . $e->getMessage());

            // А наружу — ключ + текст ошибки, чтобы ты видел в JSON.
            throw new \RuntimeException(
                'error_db_connection_failed: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    public static function fromConfigFile(string $configPath): self
    {
        /** @var mixed $config */
        $config = require $configPath;

        if (!is_array($config)) {
            throw new \RuntimeException('error_db_config_invalid');
        }

        return new self($config);
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}
