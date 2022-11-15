FROM php:7.1-cli

COPY xdebug-2x.ini /usr/local/etc/php/conf.d/xdebug.ini
COPY php.ini /usr/local/etc/php/conf.d/php.ini

RUN apt update && apt install -y mc nano git \
    && docker-php-ext-install pdo_mysql mysqli \
    && pecl install xdebug-2.8.1 && docker-php-ext-enable xdebug

COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html/
USER www-data
