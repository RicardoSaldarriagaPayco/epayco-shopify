FROM php:8.1.3-apache
ARG DEBIAN_FRONTEND=noninteractive
# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libwebp-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    zlib1g-dev \
    libjpeg-dev \
    python2.7 \
    build-essential \
    openssl \
    libssl-dev \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-configure gd --with-webp=/usr/include/webp --with-jpeg=/usr/include --with-freetype=/usr/include/freetype2/ \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install -j$(nproc) zip \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && ln -s /usr/bin/python2.7 /usr/bin/python


RUN docker-php-ext-configure gd --with-jpeg && \
    docker-php-ext-install gd

RUN docker-php-ext-install mysqli pdo pdo_mysql soap zip sockets

RUN apt-get update && \
    apt-get install -y libxslt1-dev && \
    docker-php-ext-install xsl && \
    apt-get remove -y libxslt1-dev icu-devtools libicu-dev && \
    rm -rf /var/lib/apt/lists/*


# Install extensions
RUN docker-php-ext-install mysqli mbstring exif pcntl bcmath zip
RUN docker-php-source delete

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN pecl install -f xdebug apcu \
    && docker-php-ext-enable xdebug apcu

COPY /php/dev/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

COPY config/php.ini /usr/local/etc/php/

#install git
RUN apt-get update && \
    apt-get upgrade -y && \
    apt-get install -y git
    
#install node
RUN curl -sL https://deb.nodesource.com/setup_16.x | bash -
RUN apt-get install -y nodejs
#install yarn
RUN curl -sL https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -
RUN echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list
RUN apt update && apt install yarn
WORKDIR /var/www/html
RUN a2enmod rewrite