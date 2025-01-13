#!/bin/sh

composer install --prefer-dist --no-dev --optimize-autoloader --no-interaction
php-fpm