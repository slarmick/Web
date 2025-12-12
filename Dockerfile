FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git unzip curl \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip sockets

# Установка Redis
RUN pecl install redis && docker-php-ext-enable redis

# Установка Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

# Копируем и устанавливаем зависимости
COPY ./code/composer.json ./
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# Копируем остальные файлы
COPY ./code /var/www/html

CMD ["php-fpm"]