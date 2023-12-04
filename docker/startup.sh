#!/bin/sh

# Replace LISTEN_PORT with the dynamic port assigned by Cloud Run
sed -i "s,LISTEN_PORT,$PORT,g" /etc/nginx/nginx.conf

# Start PHP-FPM
php-fpm -D

# Start Nginx
nginx