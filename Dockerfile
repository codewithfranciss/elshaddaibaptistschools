FROM php:8.2-apache

# Install dependencies for PostgreSQL PDO
RUN apt-get update && \
    apt-get install -y libpq-dev && \
    docker-php-ext-install pdo pdo_pgsql

# Copy files
COPY public/ /var/www/html/

WORKDIR /var/www/html/

EXPOSE 80
CMD ["apache2-foreground"]