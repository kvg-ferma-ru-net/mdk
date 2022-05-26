FROM php:7.3-cli

ARG user
ARG uid

RUN apt update && apt install -y libzip-dev zip mc nano netcat
RUN docker-php-ext-install pdo_mysql
RUN pecl install xdebug && docker-php-ext-enable xdebug
RUN useradd -u $uid -m $user
RUN cd ~ \
    && curl -sS https://getcomposer.org/installer -o composer-setup.php \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer

USER $uid

WORKDIR /opt/mdk/

CMD ["composer", "install"]
