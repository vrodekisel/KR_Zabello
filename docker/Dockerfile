FROM php:8.2-apache

# Включаем mod_rewrite
RUN a2enmod rewrite

# Устанавливаем PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Копируем проект в контейнер
COPY . /var/www/html

# Настройка DocumentRoot -> public/
WORKDIR /var/www/html
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf
RUN sed -i 's|DocumentRoot.*|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Права на storage
RUN chown -R www-data:www-data storage

EXPOSE 80
