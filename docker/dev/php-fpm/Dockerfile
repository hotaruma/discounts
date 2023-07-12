ARG PHP_VERSION=$PHP_VERSION

FROM php:${PHP_VERSION}-fpm-alpine

ARG user=phpfpm
ARG uid=1000
ARG gid=1000

ENV e_user=$user
ENV e_uid=$uid
ENV e_gid=$gid

RUN apk add --no-cache su-exec

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

COPY ext.ini $PHP_INI_DIR/conf.d/ext.ini
COPY local.ini $PHP_INI_DIR/conf.d/local.ini

RUN rm -f /usr/local/etc/php-fpm.d/*
COPY global.conf /usr/local/etc/php-fpm.d/
COPY www0.conf /usr/local/etc/php-fpm.d/

COPY custom-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/custom-entrypoint.sh

RUN addgroup -g $uid $user \
    && adduser -D -u $gid -G $user $user \
    && mkdir /usr/local/sessions

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

ENTRYPOINT ["custom-entrypoint.sh"]
CMD ["php-fpm"]
