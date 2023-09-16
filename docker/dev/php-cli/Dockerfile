ARG PHP_VERSION=$PHP_VERSION

FROM php:${PHP_VERSION}-cli-alpine

ARG XDEBUG_VERSION=3.2.1

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN apk add --no-cache $PHPIZE_DEPS linux-headers git \
    && pecl install xdebug-${XDEBUG_VERSION} \
	&& docker-php-ext-enable xdebug

RUN docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-install pcntl

RUN docker-php-ext-configure shmop --enable-shmop \
    && docker-php-ext-install shmop

RUN docker-php-ext-configure sockets --enable-sockets \
    && docker-php-ext-install sockets

RUN pecl install sync-1.1.3 \
	&& docker-php-ext-enable sync

COPY docker-php-ext-xdebug.ini "${PHP_INI_DIR}/conf.d/docker-php-ext-xdebug.ini"

WORKDIR /app
