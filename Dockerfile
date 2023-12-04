FROM php:7.4-fpm-alpine

# Install necessary packages
RUN apk update && apk add --no-cache libpq-dev nginx wget supervisor

# Install PHP extensions
RUN docker-php-ext-install pgsql pdo pdo_pgsql

# Adjust PHP configuration
RUN echo 'memory_limit = 2048M' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini

# Create necessary directories
RUN mkdir -p /run/nginx
RUN mkdir -p /app

# Copy Nginx configuration
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Copy application code
COPY . /app

# Install Composer
RUN wget http://getcomposer.org/composer.phar && \
    chmod a+x composer.phar && \
    mv composer.phar /usr/local/bin/composer

# Install dependencies
RUN cd /app && /usr/local/bin/composer install --no-dev

# Set ownership
RUN chown -R www-data: /app

# Copy Supervisor configuration for queue worker
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Command to start Supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]