FROM php:8.5-fpm

RUN apt-get update \
    && apt-get install -y zlib1g-dev libpng-dev libzip-dev \
    # fix pour pdftk
    && mkdir -p /usr/share/man/man1 \
    # dev
    && apt-get install -y --no-install-recommends wget vim zip git \
    # gmp
    && apt-get install -y --no-install-recommends libgmp-dev \
    # pdftk
    && apt-get install -y --no-install-recommends default-jre-headless \
    libcommons-lang3-java libbcprov-java pdftk-java \
    # redis
    && pecl install --onlyreqdeps --force redis \
    && docker-php-ext-enable redis \
    # pdo_mysql
    && docker-php-ext-install gd gmp pdo_mysql zip \
    # clean up
    && apt-get autoremove -y \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install nvm & node
SHELL ["/bin/bash", "--login", "-i", "-c"]
RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.4/install.sh | bash
RUN source /root/.bashrc && nvm install node 
RUN npm install --global yarn
SHELL ["/bin/bash", "--login", "-c"]

# install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

RUN git config --global --add safe.directory /app

# install typst
RUN set -e ; \
    mkdir -p typst/fonts ; \
    cd typst ; \
    wget https://github.com/typst/typst/releases/download/v0.15.1/typst-x86_64-unknown-linux-musl.tar.xz ; \
    tar xf typst-x86_64-unknown-linux-musl.tar.xz --strip-components=1 ; \
    rm typst-x86_64-unknown-linux-musl.tar.xz ; \
    echo "*" > .gitignore ; \
    cd fonts ; \
    wget -O DMSans.ttf https://github.com/googlefonts/dm-fonts/raw/refs/heads/main/Sans/fonts/variable/DMSans%5Bopsz,wght%5D.ttf ; \
    wget -O DMSans-Italic.ttf https://github.com/googlefonts/dm-fonts/raw/refs/heads/main/Sans/fonts/variable/DMSans-Italic%5Bopsz,wght%5D.ttf \
    ;

RUN composer global require laravel/installer

CMD [ "sh" ]
