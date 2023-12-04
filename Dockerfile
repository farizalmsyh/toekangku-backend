FROM php:7.4-fpm-alpine

RUN apk update && apk add --no-cache libpq-dev nginx wget supervisor

RUN docker-php-ext-install pgsql pdo pdo_pgsql

WORKDIR /app

RUN wget http://getcomposer.org/composer.phar && \
    chmod a+x composer.phar && \
    mv composer.phar /usr/local/bin/composer

COPY composer.json .
RUN /usr/local/bin/composer install --no-dev

COPY . .

EXPOSE 8080

CMD ["php", "artisan", "serve", "--port=8080"]