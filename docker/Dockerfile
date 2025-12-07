FROM php:8.2-apache

# Включаем mod_rewrite для "красивых" URL и роутера
RUN a2enmod rewrite

# Устанавливаем расширения для работы с MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Рабочая директория внутри контейнера
WORKDIR /var/www/html

# Копируем проект внутрь контейнера
COPY . /var/www/html

# Подменяем виртуальный хост на наш, который смотрит в public/
COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf

# Права на storage (логи и т.п.)
RUN chown -R www-data:www-data storage || true

# Открываем порт Apache
EXPOSE 80
