FROM dunglas/frankenphp:1-php8.4 AS base

WORKDIR /app/apps/symfony

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    curl \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

RUN install-php-extensions \
    pdo_pgsql \
    pgsql \
    intl \
    mbstring \
    zip \
    xml \
    dom \
    tokenizer \
    ctype \
    iconv \
    curl \
    opcache \
    apcu \
    pcntl \
    gd

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

FROM base AS dev

ENV APP_ENV=dev

RUN install-php-extensions xdebug

COPY docker/frankenphp/php.ini.dev $PHP_INI_DIR/conf.d/99-custom.ini
COPY docker/frankenphp/Caddyfile.dev /etc/frankenphp/Caddyfile
COPY docker/frankenphp/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile", "--adapter", "caddyfile"]

FROM base AS test

ENV APP_ENV=test

COPY apps/symfony /app/apps/symfony
COPY docker/frankenphp/php.ini.dev $PHP_INI_DIR/conf.d/99-custom.ini
COPY docker/frankenphp/Caddyfile.test /etc/frankenphp/Caddyfile

RUN if [ -f composer.json ]; then \
        composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts; \
        php bin/console importmap:install --no-interaction; \
    fi

CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile", "--adapter", "caddyfile"]

FROM base AS prod

ENV APP_ENV=prod

COPY apps/symfony /app/apps/symfony

COPY docker/frankenphp/php.ini.prod $PHP_INI_DIR/conf.d/99-custom.ini
COPY docker/frankenphp/Caddyfile.prod /etc/frankenphp/Caddyfile

RUN set -e; \
    if [ -f composer.json ]; then \
        composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts; \
        composer dump-autoload --optimize --classmap-authoritative; \
        php bin/console importmap:install; \
        php bin/console asset-map:compile --env=prod; \
    fi; \
    chown -R www-data:www-data /app/apps/symfony/var 2>/dev/null || true

CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile", "--adapter", "caddyfile"]
