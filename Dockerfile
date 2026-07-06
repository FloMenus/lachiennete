FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    postgresql-dev \
    git \
    unzip \
    curl \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev

RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    opcache \
    gd \
    zip \
    mbstring \
    intl

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && apk del .build-deps

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

ENV APP_ENV=prod
ENV APP_SECRET=buildsecret
ENV DATABASE_URL="postgresql://app:!ChangeMe!@127.0.0.1:5432/app?serverVersion=16&charset=utf8"

RUN mkdir -p var/cache var/log public/uploads/articles public/uploads/misc

RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts --ignore-platform-req=ext-amqp --ignore-platform-req=ext-redis

RUN echo "APP_ENV=prod" > .env
RUN php bin/console importmap:install
RUN php bin/console tailwind:build --minify

RUN chown -R www-data:www-data var public/uploads

EXPOSE 8000

ENTRYPOINT ["sh", "docker/entrypoint.prod.sh"]
