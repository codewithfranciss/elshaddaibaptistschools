FROM php:8.2-apache

# Install PostgreSQL extension dependencies
RUN apt-get update && \
    apt-get install -y libpq-dev && \
    docker-php-ext-install pdo pdo_pgsql

# Set Apache to listen on 8080
RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

# Suppress ServerName warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Copy project files
COPY public/ /var/www/html/

WORKDIR /var/www/html/

EXPOSE 8080

CMD ["apache2-foreground"]