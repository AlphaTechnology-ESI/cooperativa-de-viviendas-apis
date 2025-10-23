#!/bin/sh

cd /var/www

php-fpm &

nginx -g 'daemon off;'