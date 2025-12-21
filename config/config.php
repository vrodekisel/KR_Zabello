<?php

declare(strict_types=1);

return [
    'host' => getenv('DB_HOST') ?: 'db',

    'port' => (int) (getenv('DB_PORT') ?: 3306),

    'dbname' => getenv('DB_DATABASE') ?: 'ingame_content_voting',

    'username' => getenv('DB_USERNAME') ?: 'root',

    'password' => getenv('DB_PASSWORD') ?: 'root',

    'charset' => 'utf8mb4',
];
