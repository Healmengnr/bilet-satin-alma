FROM php:8.3-apache

# SQLite ve gerekli PHP extension'larını yükle
RUN apt-get update && apt-get install -y \
    sqlite3 \
    libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

# Apache mod_rewrite'ı etkinleştir
RUN a2enmod rewrite

# Çalışma dizinini ayarla
WORKDIR /var/www/html

# Uygulama dosyalarını kopyala
COPY . /var/www/html/

# Apache konfigürasyonu
COPY docker/apache-config.conf /etc/apache2/sites-available/000-default.conf

# Veritabanı dizini için izin ver
RUN mkdir -p /var/www/html/database && chmod 777 /var/www/html/database

# Apache'yi başlat
EXPOSE 80
CMD ["apache2-foreground"]
