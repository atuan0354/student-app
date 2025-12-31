FROM php:8.2-apache

# Enable apache rewrite + install php extensions commonly needed with MySQL
RUN a2enmod rewrite && \
    docker-php-ext-install mysqli pdo pdo_mysql

# Copy source code into Apache web root
COPY . /var/www/html/

# Set permissions (basic)
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

EXPOSE 80
