<?php

declare(strict_types=1);

return [
    // Ключ названия приложения, реальный текст будет в языковых файлах
    'app_name_key' => getenv('APP_NAME_KEY') ?: 'app.name.ingame_content_voting',

    'env'      => getenv('APP_ENV') ?: 'local',

    'debug'    => getenv('APP_DEBUG') === 'true',

    // Это техничка, не интерфейс, можно оставить URL как есть
    'base_url' => getenv('APP_URL') ?: 'http://localhost',

    // Путь к логам — тоже чисто техническая штука
    'log_path' => __DIR__ . '/../storage/logs/app.log',

    // Локализация
    'locale'           => getenv('APP_LOCALE') ?: 'en',
    'fallback_locale'  => 'en',
    'available_locales' => ['en', 'ru'],

    'timezone' => getenv('APP_TIMEZONE') ?: 'UTC',
];
