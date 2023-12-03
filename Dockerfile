FROM php:7.4-fpm-alpine

RUN apk update && apk add --no-cache libpq-dev && docker-php-ext-install pgsql pdo pdo_pgsql
RUN apk add --no-cache nginx wget

RUN echo 'memory_limit = 2048M' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini
RUN mkdir -p /run/nginx

COPY docker/nginx.conf /etc/nginx/nginx.conf

RUN mkdir -p /app
COPY . /app

RUN sh -c "wget http://getcomposer.org/composer.phar && chmod a+x composer.phar && mv composer.phar /usr/local/bin/composer"

RUN cd /app && \
  /usr/local/bin/composer install --no-dev

RUN chown -R www-data: /app

CMD php artisan queue:work & sh /app/docker/startup.sh