<?php

declare(strict_types=1);

return [
    // Параметры подключения к MySQL.
    // В Docker-сети хост базы — это имя сервиса: "db".
    'host'    => getenv('DB_HOST') ?: 'db',
    'port'    => (int) (getenv('DB_PORT') ?: 3306),
    'dbname'  => getenv('DB_DATABASE') ?: 'ingame_content_voting',

    // ВАЖНО: ключ должен называться именно 'user', а не 'username',
    // потому что MySQLConnection читает $config['user'].
    'user'     => getenv('DB_USERNAME') ?: 'app_user',
    'password' => getenv('DB_PASSWORD') ?: 'secret',

    // На всякий случай явно задаём charset
    'charset' => 'utf8mb4',
];
