FROM php:7.4-fpm-bullseye

# Use archived Debian repositories
RUN rm -f /etc/apt/sources.list.d/* \
    && printf '%s\n' \
        'deb http://archive.debian.org/debian bullseye main' \
        'deb http://archive.debian.org/debian bullseye-updates main' \
        'deb http://snapshot.debian.org/archive/debian-security/20260903T220410Z bullseye-security main' \
        > /etc/apt/sources.list \
    && printf 'Acquire::Check-Valid-Until "false";\n' \
        > /etc/apt/apt.conf.d/99no-check-valid-until

RUN apt-get update && apt-get install -y --no-install-recommends \
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
    curl \
    && rm -rf /var/lib/apt/lists/*

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

# FROM php:8.5-fpm

# RUN apt-get update && apt-get install -y \
#     libpq-dev \
#     libzip-dev \
#     libicu-dev \
#     libpng-dev \
#     libonig-dev \
#     libjpeg62-turbo-dev \
#     libfreetype6-dev \
#     zip \
#     unzip \
#     git \
#     curl

# RUN docker-php-ext-configure gd \
#     --with-freetype \
#     --with-jpeg

# RUN docker-php-ext-install \
#     pdo \
#     pdo_mysql \
#     pdo_pgsql \
#     pgsql \
#     mysqli \
#     mbstring \
#     bcmath \
#     intl \
#     gd \
#     zip

# COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# WORKDIR /app