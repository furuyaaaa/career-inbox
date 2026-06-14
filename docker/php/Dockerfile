FROM php:8.4-fpm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        python3 \
        libicu-dev \
        libpq-dev \
        libzip-dev \
        unzip \
        zip \
    && docker-php-ext-install \
        bcmath \
        intl \
        opcache \
        pdo_pgsql \
        pgsql \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY php.ini /usr/local/etc/php/conf.d/career-inbox.ini
COPY entrypoint.sh /usr/local/bin/career-inbox-entrypoint

WORKDIR /var/www/html

RUN chmod +x /usr/local/bin/career-inbox-entrypoint

ENTRYPOINT ["career-inbox-entrypoint"]
CMD ["php-fpm"]
