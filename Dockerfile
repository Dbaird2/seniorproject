<<<<<<< HEAD
# Use official PHP image
FROM php:8.2-cli

# Install dependencies for Composer (if needed)
RUN apt-get update && apt-get install -y unzip git

# Install Composer globally
=======
FROM php:8.2-cli

# Install dependencies
RUN apt-get update && apt-get install -y unzip git libcurl4-openssl-dev libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-install curl zip gd pdo pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
>>>>>>> ceedf7a (Dockerfile)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

<<<<<<< HEAD
# Copy everything into container
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader
=======
# Copy application files
COPY . .

# Clear Composer cache and install dependencies
RUN composer clear-cache
RUN composer install --no-dev --optimize-autoloader --verbose
>>>>>>> ceedf7a (Dockerfile)

# Expose the port that Render expects
EXPOSE 10000

<<<<<<< HEAD
# Start PHP server when container runs
=======
# Command to start the PHP built-in server
>>>>>>> ceedf7a (Dockerfile)
CMD ["php", "-S", "0.0.0.0:10000", "-t", "public"]
