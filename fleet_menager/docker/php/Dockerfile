# --------------------------------------------------------------------------
# Fleet Manager - obraz PHP-FPM 8.3 (Alpine)
# Zawiera rozszerzenia wymagane przez Laravel 12 + Composer + Node 20.
# --------------------------------------------------------------------------
FROM php:8.3-fpm-alpine

# Narzędzia systemowe i biblioteki natywne potrzebne do build-u rozszerzeń.
RUN apk add --no-cache \
        bash \
        git \
        curl \
        zip unzip \
        icu-dev \
        oniguruma-dev \
        libzip-dev \
        libpng-dev libjpeg-turbo-dev freetype-dev \
        nodejs npm \
        $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        bcmath \
        intl \
        zip \
        gd \
        opcache \
    && apk del $PHPIZE_DEPS \
    && rm -rf /var/cache/apk/*

# Composer (z oficjalnego obrazu)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Konfiguracja PHP - sensowne ustawienia dla developera
COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-fleet.ini

WORKDIR /var/www/html

# Skrypt startowy - czeka na MySQL, instaluje zależności i migruje.
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

EXPOSE 9000
ENTRYPOINT ["entrypoint"]
CMD ["php-fpm"]
