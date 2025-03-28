# Start from a base PHP 8.1 image
FROM php:8.1

# Install necessary packages
RUN apt-get update \
    && apt-get install -y \
    nodejs \
    npm \
    redis-server \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# Install and enable exif PHP extension
RUN docker-php-ext-install exif \
    && docker-php-ext-enable exif

# Install Redis PHP extension
RUN pecl install redis && docker-php-ext-enable redis

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install the latest Node.js and npm
RUN curl -sL https://deb.nodesource.com/setup_16.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm@latest

# Set the working directory
WORKDIR /app

# Clone the Laravel project from Github
RUN git clone https://github.com/Omar630603/ValidateNodeJS.git .

# Install dependencies
RUN composer install --no-interaction --no-scripts --no-progress \
    && npm install \
    && npm run build

# Copy the environment files
COPY public/assets/projects/api-experiment/files/.env /app/public/assets/projects/api-experiment/files/.env
COPY public/assets/projects/auth-experiment/files/.env /app/public/assets/projects/auth-experiment/files/.env
COPY .env /app/.env

# Create MySQL database
RUN apt-get update \
    && apt-get install -y mysql-client \
    && docker-php-ext-install pdo_mysql \
    && mysql -e "CREATE DATABASE validatenodejs;"

# Migrate and seed the database
RUN php artisan migrate --seed

# Start Redis server and Laravel queue
CMD service redis-server start && php artisan queue:work redis
