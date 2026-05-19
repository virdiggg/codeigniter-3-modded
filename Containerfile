FROM php:7.4-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    libpng-dev \
    libonig-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    curl

RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg

RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    pgsql \
    mysqli \
    mbstring \
    bcmath \
    intl \
    gd \
    zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app