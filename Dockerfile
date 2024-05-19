# Use an official PHP runtime as a parent image with Apache
FROM php:7.4-apache

# Set working directory
WORKDIR /var/www/html

# Install any needed extensions and other dependencies
RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy the current directory contents into the container at /var/www/html
COPY . /var/www/html/

# Run Composer to install dependencies from your composer.json
RUN composer install

# Expose port 80 to the outside once the container is running
EXPOSE 80

# Start Apache server
CMD ["apache2-foreground"]
