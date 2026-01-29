# syntax=docker/dockerfile:1

################################################################################
# Stage 1: Install Composer dependencies
################################################################################
FROM composer:lts as deps

WORKDIR /app

# Install system dependencies needed for PHP extensions in Alpine
RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    libsodium-dev \
    zip

# Download dependencies - leverage Docker cache
RUN --mount=type=bind,source=composer.json,target=composer.json \
    --mount=type=bind,source=composer.lock,target=composer.lock \
    --mount=type=cache,target=/tmp/cache \
    composer install --no-interaction --no-scripts --ignore-platform-req=ext-zip

################################################################################
# Stage 2: Final runtime image
################################################################################
FROM php:8.4-apache as final

# Install system dependencies (Debian-based, so use apt-get)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    libsodium-dev \
    zip \
    && docker-php-ext-install \
        pcntl \
        pdo_pgsql \
        sodium \
        zip \
    && docker-php-ext-enable pcntl pdo_pgsql sodium zip \
    && rm -rf /var/lib/apt/lists/*

# Use production PHP configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Enable Apache modules
RUN a2enmod rewrite && a2enmod headers

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

# Copy Apache configuration
COPY apache.conf /etc/apache2/sites-available/000-default.conf

# Copy dependencies from deps stage
COPY --from=deps /app/vendor/ /var/www/html/vendor

# Copy application files
COPY . /var/www/html

# Fix permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

USER www-data