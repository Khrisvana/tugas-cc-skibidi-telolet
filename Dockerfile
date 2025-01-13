FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

RUN apk update
RUN apk --no-cache add curl linux-headers libzip-dev zip
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN docker-php-ext-install pdo pdo_mysql ftp

RUN echo "upload_tmp_dir = /tmp" >> /usr/local/etc/php/php.ini

EXPOSE 9000

ENTRYPOINT [ "./run.sh" ]
