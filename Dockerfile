# Base image: PHP 8.1 with Apache
FROM php:8.1-apache

# Install dependencies for PHP Zip extension
RUN apt-get update && apt-get install -y libzip-dev && docker-php-ext-install zip

# Enable mod_rewrite (optional, useful for routing)
RUN a2enmod rewrite

# Copy all PHP files to web root
COPY . /var/www/html/

# Set correct permissions (optional)
RUN chown -R www-data:www-data /var/www/html

# Expose port 80
EXPOSE 80