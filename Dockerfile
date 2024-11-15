FROM php:8.1-apache

# Instalacja nano, zależności, i rozszerzeń PHP wymaganych przez Slim i PostgreSQL
RUN apt-get update && \
    apt-get install -y nano libzip-dev unzip libpq-dev && \
    docker-php-ext-install zip pdo_pgsql

# Instalacja Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Ustawienie katalogu roboczego
WORKDIR /var/www/html

# Kopiowanie composer.json
COPY composer.json ./

# Jeśli composer.lock istnieje lokalnie, skopiuje go do obrazu
RUN if [ -f composer.lock ]; then cp composer.lock .; fi

# Uruchomienie instalacji zależności za pomocą Composer
RUN composer install

# Skopiowanie lokalnych plików projektu do kontenera
COPY . .

# Tworzenie katalogów templates_c i cache oraz nadanie uprawnień
RUN mkdir -p /var/www/html/templates_c /var/www/html/cache && \
    chmod -R 777 /var/www/html/templates_c /var/www/html/cache && \
    chown -R root:www-data /var/www/html

# Zmiana katalogu root na public
RUN echo "DocumentRoot /var/www/html/public" > /etc/apache2/sites-available/000-default.conf

# Ustawienie uprawnień dla Apache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Włączenie mod_rewrite dla Apache (ważne dla Slim)
RUN a2enmod rewrite

# Konfiguracja DirectoryIndex w Apache
RUN echo "DirectoryIndex index.php index.html" > /etc/apache2/conf-available/directory-index.conf && \
    a2enconf directory-index

# Eksponowanie portu 80 dla Apache
EXPOSE 80
