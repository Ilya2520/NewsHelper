FROM php:8.2-fpm

WORKDIR /app

RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libpq-dev \
    libxml2-dev \
    libicu-dev && docker-php-ext-install intl

RUN apt-get update && \
    apt-get install -y libz-dev && \
    pecl install redis && \
    docker-php-ext-enable redis


RUN docker-php-ext-install pdo pdo_pgsql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install --prefer-dist --no-scripts --no-interaction

EXPOSE 9000
CMD ["php-fpm"]
