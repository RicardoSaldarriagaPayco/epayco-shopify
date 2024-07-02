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

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin/
RUN install-php-extensions redis

# Install extensions
RUN docker-php-ext-install mysqli mbstring exif pcntl bcmath zip
RUN docker-php-source delete

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN pecl install xdebug && docker-php-ext-enable xdebug
COPY /php/dev/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

COPY config/php.ini /usr/local/etc/php/

COPY www/epayco /var/www/html

COPY docker/ShopifyIntermediateCertificate.crt /etc/ssl/certs/ShopifyIntermediateCertificate.crt
COPY docker/ShopifyNewPaymentsPlatformRoot.crt /etc/ssl/certs/ShopifyNewPaymentsPlatformRoot.crt
COPY docker/ShopifyPaymentsPlatformRoot.crt /etc/ssl/certs/ShopifyPaymentsPlatformRoot.crt
COPY docker/ShopifySecondaryProduction.crt /etc/ssl/certs/ShopifySecondaryProduction.crt

COPY docker/etc/apache2/sites-enabled/000-default.conf /etc/apache2/sites-enabled/000-default.conf
COPY docker/etc/apache2/sites-available/default-ssl.conf /etc/apache2/sites-available/default-ssl.conf


WORKDIR /var/www/html
RUN a2enmod headers rewrite