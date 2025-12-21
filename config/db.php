<?php

declare(strict_types=1);

return [
    'host'    => getenv('DB_HOST') ?: 'db',
    'port'    => (int) (getenv('DB_PORT') ?: 3306),
    'dbname'  => getenv('DB_DATABASE') ?: 'ingame_content_voting',

    'user'     => getenv('DB_USERNAME') ?: 'app_user',
    'password' => getenv('DB_PASSWORD') ?: 'secret',

    'charset' => 'utf8mb4',
];
