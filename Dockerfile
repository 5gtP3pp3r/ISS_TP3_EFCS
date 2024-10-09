FROM php:fpm-alpine
RUN apk update && \
    apk upgrade && \
    docker-php-ext-install mysqli
