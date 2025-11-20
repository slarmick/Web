FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install \
    pdo \
    pdo_mysql \
    mysqli \
    zip \
    gd \
    mbstring \
    xml

# Устанавливаем Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Копируем зависимости сначала для кэширования
COPY code/composer.json code/composer.lock ./
RUN composer install --no-dev --optimize-autoloader

# Копируем исходный код
COPY code/ .

CMD ["php-fpm"]