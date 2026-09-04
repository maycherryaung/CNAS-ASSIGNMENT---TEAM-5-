# Start with PHP 8.3 and Apache pre-installed
FROM php:8.3-apache

# Update system packages for security
RUN apt-get update && apt-get upgrade -y && rm -rf /var/lib/apt/lists/*

RUN set -eux; \
    # Install MySQL database drivers so PHP can connect to the database
    docker-php-ext-install mysqli pdo_mysql; \
    # Enable Apache modules for headers and URL rewriting
    a2enmod headers rewrite; \
    # Change Apache from port 80 to 8080 (Kubernetes requirement)
    sed -ri 's/^Listen 80$/Listen 8080/' /etc/apache2/ports.conf; \
    sed -ri 's/<VirtualHost \*:80>/<VirtualHost *:8080>/' /etc/apache2/sites-available/000-default.conf; \
    # Configure security headers to protect against attacks
    { \
        # Hide Apache version from attackers
        echo 'ServerTokens Prod'; \
        # Remove Apache signature from error pages
        echo 'ServerSignature Off'; \
        # Prevent browser from guessing file types (blocks MIME-sniffing attacks)
        echo 'Header always set X-Content-Type-Options "nosniff"'; \
        # Block site from being embedded in frames (prevents clickjacking)
        echo 'Header always set X-Frame-Options "DENY"'; \
        # Limit info sent to other sites when users click links
        echo 'Header always set Referrer-Policy "strict-origin-when-cross-origin"'; \
        # Disable camera, microphone, and geolocation access (not needed)
        echo 'Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"'; \
    } > /etc/apache2/conf-available/cnas-security.conf; \
    a2enconf cnas-security; \
    # Change user ID to 1000 (required for Kubernetes)
    groupmod -g 1000 www-data; \
    usermod -u 1000 -g 1000 www-data; \
    # Create Apache directories
    mkdir -p /var/run/apache2 /var/lock/apache2 /var/log/apache2; \
    # Give the www-data user ownership of all files
    chown -R www-data:www-data /var/www/html /var/run/apache2 /var/lock/apache2 /var/log/apache2

# Copy PHP and CSS files into the container
COPY --chown=www-data:www-data *.php *.css /var/www/html/

# Run as non-root user (more secure)
USER www-data

# Container listens on port 8080
EXPOSE 8080

# Monitor app health: check every 30s, wait 5s for response, skip first 20s, restart after 3 failures
HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD php -r "exit(@file_get_contents('http://127.0.0.1:8080/health.php') === false ? 1 : 0);"

# Start Apache when container runs
CMD ["apache2-foreground"]

