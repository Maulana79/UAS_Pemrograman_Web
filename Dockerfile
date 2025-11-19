# Gunakan image PHP resmi dengan Apache
FROM php:8.2-apache

# Install driver PostgreSQL (libpq-dev) yang wajib untuk Supabase
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Aktifkan mod_rewrite Apache (Supaya .htaccess jalan)
RUN a2enmod rewrite

# Copy semua kodingan kamu ke dalam container
COPY . /var/www/html/

# Atur hak akses folder agar Apache bisa baca
RUN chown -R www-data:www-data /var/www/html

# Expose port 80 (Port standar web)
EXPOSE 80