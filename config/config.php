<?php

declare(strict_types=1);

return [
    // Хост БД
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    // Порт БД
    'port' => (int) (getenv('DB_PORT') ?: 3306),
    // Имя базы данных
    'dbname' => getenv('DB_DATABASE') ?: 'ingame_content_voting',
    // Имя пользователя БД
    'username' => getenv('DB_USERNAME') ?: 'root',
    // Пароль пользователя БД
    'password' => getenv('DB_PASSWORD') ?: '',
    // Кодировка
    'charset' => 'utf8mb4',
];
