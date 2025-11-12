FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    && docker-php-ext-install \
    pdo \
    pdo_mysql \
    mysqli \
    zip \
    gd \
    mbstring

# Устанавливаем Composer ПРАВИЛЬНО
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

# Копируем код
COPY code/ .

CMD ["php-fpm"]