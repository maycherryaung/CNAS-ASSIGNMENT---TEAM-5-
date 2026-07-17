FROM php:8.3-apache

RUN set -eux; \
    docker-php-ext-install mysqli pdo_mysql; \
    a2enmod headers rewrite; \
    sed -ri 's/^Listen 80$/Listen 8080/' /etc/apache2/ports.conf; \
    sed -ri 's/<VirtualHost \*:80>/<VirtualHost *:8080>/' /etc/apache2/sites-available/000-default.conf; \
    { \
        echo 'ServerTokens Prod'; \
        echo 'ServerSignature Off'; \
        echo 'Header always set X-Content-Type-Options "nosniff"'; \
        echo 'Header always set X-Frame-Options "DENY"'; \
        echo 'Header always set Referrer-Policy "strict-origin-when-cross-origin"'; \
        echo 'Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"'; \
    } > /etc/apache2/conf-available/cnas-security.conf; \
    a2enconf cnas-security; \
    mkdir -p /var/run/apache2 /var/lock/apache2 /var/log/apache2; \
    chown -R www-data:www-data /var/www/html /var/run/apache2 /var/lock/apache2 /var/log/apache2

COPY --chown=www-data:www-data *.php *.css /var/www/html/

USER www-data
EXPOSE 8080

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD php -r "exit(@file_get_contents('http://127.0.0.1:8080/health.php') === false ? 1 : 0);"

CMD ["apache2-foreground"]

