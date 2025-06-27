# Use official PHP image with Apache
FROM php:8.1-apache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy all project files to the container
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Set recommended permissions
RUN chown -R www-data:www-data /var/www/html

# Expose default Apache port
EXPOSE 80
