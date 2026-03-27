FROM php:8.4-fpm

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    curl \
    git \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libonig-dev \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    unzip \
    zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
    bcmath \
    gd \
    mbstring \
    pdo_mysql \
    xml \
    zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

COPY docker/php/entrypoint.sh /usr/local/bin/docker-entrypoint

RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint \
    && chmod +x /usr/local/bin/docker-entrypoint

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/docker-entrypoint"]
CMD ["php-fpm"]
