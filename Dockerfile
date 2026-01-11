FROM php:8.4-fpm

RUN apt update && apt install -y zlib1g-dev libpng-dev libzip-dev && rm -rf /var/lib/apt/lists/*

RUN mkdir -p /usr/share/man/man1 \
    && apt-get update \
    && apt-get install -y --no-install-recommends curl wget \
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
    && rm -rf /tmp/pear/

# Install nvm & node
SHELL ["/bin/bash", "--login", "-i", "-c"]
RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.2/install.sh | bash
RUN source /root/.bashrc && nvm install node 
RUN npm install --global yarn
SHELL ["/bin/bash", "--login", "-c"]

# install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# install typst
RUN set -e ; \
    wget https://github.com/typst/typst/releases/download/v0.13.1/typst-x86_64-unknown-linux-musl.tar.xz ; \
    tar xf typst-x86_64-unknown-linux-musl.tar.xz ; \
    mkdir typst ; \
    mv typst-x86_64-unknown-linux-musl/* typst ; \
    echo "*" > typst/.gitignore ; \
    rm -rf typst-x86_64-unknown-linux-musl* \
    ;

RUN composer global require laravel/installer

CMD [ "sh" ]
