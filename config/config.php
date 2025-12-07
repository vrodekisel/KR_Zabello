<?php

declare(strict_types=1);

return [
    // Хост БД: для Docker по умолчанию стучимся в сервис "db"
    'host' => getenv('DB_HOST') ?: 'db',

    // Порт БД
    'port' => (int) (getenv('DB_PORT') ?: 3306),

    // Имя базы данных
    'dbname' => getenv('DB_DATABASE') ?: 'ingame_content_voting',

    // Имя пользователя БД
    'username' => getenv('DB_USERNAME') ?: 'root',

    // Пароль пользователя БД (чаще всего "root" из docker-compose)
    'password' => getenv('DB_PASSWORD') ?: 'root',

    // Кодировка
    'charset' => 'utf8mb4',
];
