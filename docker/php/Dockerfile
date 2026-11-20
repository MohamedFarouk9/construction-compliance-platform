FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
        icu-libs \
        libzip \
        git \
        unzip \
        curl \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        icu-dev \
        libzip-dev \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        intl \
        zip \
        opcache \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-buildguard.ini

WORKDIR /var/www/html
