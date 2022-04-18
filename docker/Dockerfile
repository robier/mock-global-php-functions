FROM php:7.3-cli-alpine

ARG HOST_USER_ID
ARG HOST_GROUP_ID

RUN apk --update --no-cache add \
        autoconf \
        build-base \
        bzip2-dev \
        zlib-dev \
        git \
        unzip

# PECL extensions
RUN pecl install xdebug && \
    docker-php-ext-enable xdebug

# Composer
RUN curl --show-error https://getcomposer.org/installer | php -- \
    --filename=composer \
    --install-dir=/usr/bin

RUN addgroup -g ${HOST_GROUP_ID} app && \
    adduser -s /bin/sh/ -D -u ${HOST_USER_ID} -G app app

USER app

RUN mkdir -p /home/app/src

VOLUME /home/app/src
WORKDIR /home/app/src
