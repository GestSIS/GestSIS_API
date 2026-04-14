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

# install typst
RUN set -e ; \
    wget https://github.com/typst/typst/releases/download/v0.13.1/typst-x86_64-unknown-linux-musl.tar.xz ; \
    tar xf typst-x86_64-unknown-linux-musl.tar.xz ; \
    mkdir typst ; \
    mv typst-x86_64-unknown-linux-musl/* typst ; \
    echo "*" > typst/.gitignore ; \
    rm -rf typst-x86_64-unknown-linux-musl* \
    ;

WORKDIR /app

# Installs fonts for typst to use
RUN mkdir typst/fonts && \
    cd typst/fonts && \
    wget https://github.com/googlefonts/dm-fonts/raw/refs/heads/main/Sans/fonts/ttf/DMSans-Bold.ttf \
    https://github.com/googlefonts/dm-fonts/raw/refs/heads/main/Sans/fonts/ttf/DMSans-BoldItalic.ttf \
    https://github.com/googlefonts/dm-fonts/raw/refs/heads/main/Sans/fonts/ttf/DMSans-Italic.ttf \
    https://github.com/googlefonts/dm-fonts/raw/refs/heads/main/Sans/fonts/ttf/DMSans-Medium.ttf \
    https://github.com/googlefonts/dm-fonts/raw/refs/heads/main/Sans/fonts/ttf/DMSans-MediumItalic.ttf \
    https://github.com/googlefonts/dm-fonts/raw/refs/heads/main/Sans/fonts/ttf/DMSans-Regular.ttf \
    ;

RUN composer global require laravel/installer

CMD [ "sh" ]
