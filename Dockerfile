FROM php:8.2-cli

RUN apt-get update && apt-get install -y libpq-dev && \
    docker-php-ext-install pdo_pgsql
# Install dependencies and GD extension
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-install gd curl zip pdo pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy application files
COPY . .

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader --verbose

# Expose the port that Render expects
EXPOSE 8080

# Command to start the PHP built-in server
# CMD ["php", "-S", "0.0.0.0:10000", "-t", "."]
CMD ["sh", "-c", "php -S 0.0.0.0:$PORT -t public"]
