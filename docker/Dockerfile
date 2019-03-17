FROM php:7.1-cli-alpine

RUN set -xe && \
    apk add --no-cache --update git && \
    apk add --no-cache --update --virtual .phpize-deps ${PHPIZE_DEPS} && \
    # download and install composer
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --install-dir=/usr/bin --filename=composer && \
    php -r "unlink('composer-setup.php');" && \
    # update pecl
    pecl channel-update pecl.php.net && \
    # install pecl extensions
    pecl install xdebug && \
    # enable extensions installed via pecl
    docker-php-ext-enable xdebug && \
    # cleanup
    apk del --purge .phpize-deps && \
    rm -rf /tmp/* && \
    rm -rf /usr/share/php7 && \
    rm -rf /var/cache/apk/*
