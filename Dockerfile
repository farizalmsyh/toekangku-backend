FROM php:7.4-fpm-alpine

RUN apk update && apk add --no-cache libpq-dev nginx wget supervisor

RUN docker-php-ext-install pgsql pdo pdo_pgsql

WORKDIR /app

COPY composer.json .
RUN composer install --no-dev

COPY . .

EXPOSE 8080

CMD ["php", "artisan", "serve", "--port=8080"]