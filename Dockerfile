# Use the official PHP 8.2 FPM Alpine image
FROM php:8.2-fpm-alpine

# Install necessary PHP extensions
RUN apk add --no-cache \
    curl \
    git \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html

# Expose PHP-FPM port
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]
