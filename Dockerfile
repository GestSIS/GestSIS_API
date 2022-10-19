FROM php:8.0-fpm

RUN mkdir -p /usr/share/man/man1 \
    && apt-get update \
    && apt-get install -y --no-install-recommends npm \
    && docker-php-ext-install gd \
    # gmp
    && apt-get install -y --no-install-recommends libgmp-dev \
    && docker-php-ext-install gmp \
    # pdftk
    && apt-get install -y --no-install-recommends pdftk \
    # pdo_mysql
    && docker-php-ext-install pdo_mysql \
    # opcache
    && docker-php-ext-enable opcache \
    # zip
    && docker-php-ext-install zip \
    # clean up
    && apt-get autoclean -y \
    && rm -rf /var/lib/apt/lists/* \
    && rm -rf /tmp/pear/ \
    && npm add -g yarn

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer global require laravel/installer

CMD [ "sh" ]
