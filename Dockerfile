FROM php:8.2-apache

# Enable PostgreSQL support
RUN docker-php-ext-install pdo pdo_pgsql

# Copy all files to Apache root
COPY public/ /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]