FROM php:8.2-cli

# Install ekstensi yang dibutuhkan Laravel, PostgreSQL, dan GD (untuk upload gambar)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql pdo_mysql gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Atur folder kerja di dalam server
WORKDIR /app

# Salin seluruh kode aplikasi Anda ke dalam server
COPY . .

# Install dependensi Laravel
RUN composer install --optimize-autoloader

# Jalankan server menggunakan port yang diberikan oleh Render
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT
