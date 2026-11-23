# syntax=docker/dockerfile:1
FROM dunglas/frankenphp:1-php8.4 AS base

WORKDIR /app/apps/symfony

RUN apt-get update && apt-get upgrade -y && apt-get install -y --no-install-recommends \
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

RUN install-php-extensions pcov

COPY docker/frankenphp/php.ini.dev $PHP_INI_DIR/conf.d/99-custom.ini
COPY docker/frankenphp/Caddyfile.test /etc/frankenphp/Caddyfile

COPY apps/symfony/composer.json apps/symfony/composer.lock* apps/symfony/symfony.lock* ./
RUN --mount=type=cache,target=/root/.cache/composer \
    composer install --no-interaction --prefer-dist --no-scripts --no-autoloader

COPY apps/symfony /app/apps/symfony

RUN composer dump-autoload --optimize \
    && php bin/console importmap:install --no-interaction

CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile", "--adapter", "caddyfile"]

FROM base AS prod

ENV APP_ENV=prod

ARG USER=app

COPY docker/frankenphp/php.ini.prod $PHP_INI_DIR/conf.d/99-custom.ini
COPY docker/frankenphp/Caddyfile.prod /etc/frankenphp/Caddyfile

COPY apps/symfony/composer.json apps/symfony/composer.lock* apps/symfony/symfony.lock* ./
RUN --mount=type=cache,target=/root/.cache/composer \
    composer install --no-dev --no-interaction --prefer-dist --no-scripts --no-autoloader

COPY apps/symfony /app/apps/symfony

RUN set -e; \
    composer dump-autoload --no-dev --optimize --classmap-authoritative; \
    SENTRY_DSN= php bin/console importmap:install; \
    SENTRY_DSN= php bin/console asset-map:compile --env=prod; \
    mkdir -p var; \
    dpkg --purge --force-depends linux-libc-dev; \
    useradd --system --uid 1000 --no-create-home ${USER}; \
    setcap CAP_NET_BIND_SERVICE=+ep /usr/local/bin/frankenphp; \
    mkdir -p /data/caddy /config/caddy; \
    chown -R ${USER}:${USER} /data/caddy /config/caddy /app/apps/symfony/var

USER ${USER}

CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile", "--adapter", "caddyfile"]
