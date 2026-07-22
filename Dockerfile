FROM php:8.2-apache

# Listen on 8080 so a non-root user can bind it
RUN sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf && \
    sed -i 's/:80/:8080/' /etc/apache2/sites-available/000-default.conf

# Create a non-root user and set proper permissions
RUN groupadd -g 1000 appgroup && \
    useradd -u 1000 -g appgroup -m appuser

# Install MySQL driver
RUN docker-php-ext-install pdo pdo_mysql

# Build code into image - Kubernetes has no bind mount
COPY --chown=appuser:appgroup *.php /var/www/html

# Switch to the non-root user
USER appuser
EXPOSE 8080
