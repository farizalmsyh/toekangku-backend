FROM php:7.4-fpm-alpine

# Install Supervisor
RUN apk update && apk add --no-cache supervisor

# Install PostgreSQL dependencies
RUN apk add --no-cache libpq-dev && docker-php-ext-install pgsql pdo pdo_pgsql

# Install Nginx and wget
RUN apk add --no-cache nginx wget

# Set memory limit
RUN echo 'memory_limit = 2048M' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini

# Create Nginx run directory
RUN mkdir -p /run/nginx

# Copy Nginx configuration file
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Create application directory
RUN mkdir -p /app

# Copy project files to application directory
COPY . /app

# Download and install Composer
RUN sh -c "wget http://getcomposer.org/composer.phar && chmod a+x composer.phar && mv composer.phar /usr/local/bin/composer"

# Install Composer dependencies (excluding dev dependencies)
RUN cd /app && \
    /usr/local/bin/composer install --no-dev

# Set application directory ownership
RUN chown -R www-data: /app

# Install Telescope
RUN php artisan telescope:install

# Replace LISTEN_PORT placeholder with actual port
RUN sed -i "s,LISTEN_PORT,$PORT,g" /etc/nginx/nginx.conf

# Start Supervisor in background
RUN echo 'supervisorctl start all' >> /app/docker/startup.sh

# Configure Supervisor to manage php-fpm
RUN echo '[program:php-fpm] \n\
    process_name=%(program_name)s \n\
    directory=/usr/local/bin \n\
    command=php-fpm -D \n\
    autostart=true \n\
    autorestart=true \n\
    stdout_logfile=/dev/stdout \n\
    stderr_logfile=/dev/stderr' >> /etc/supervisor/conf.d/php-fpm.conf

# Configure Supervisor to manage queue worker
RUN echo '[program:queue-worker] \n\
    process_name=%(program_name)s \n\
    directory=/usr/local/bin \n\
    command=php artisan queue:work \n\
    autostart=true \n\
    autorestart=true \n\
    stdout_logfile=/dev/stdout \n\
    stderr_logfile=/dev/stderr' >> /etc/supervisor/conf.d/queue-worker.conf

# Set startup script to start Supervisor and wait for Nginx to start
CMD sh /app/docker/startup.sh