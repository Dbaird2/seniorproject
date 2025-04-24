# Use official PHP image
FROM php:8.2-cli

# Install dependencies for Composer (if needed)
RUN apt-get update && apt-get install -y unzip git

# Install Composer globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy everything into container
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Expose the port that Render expects
EXPOSE 10000

# Start PHP server when container runs
CMD ["php", "-S", "0.0.0.0:10000", "-t", "public"]
