FROM php:8.2-fpm

# Installation des extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libssl-dev \
    pkg-config \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && rm -rf /var/lib/apt/lists/*

# Installation de l'extension MongoDB
RUN pecl install mongodb-1.17.0 \
    && docker-php-ext-enable mongodb

# Installation de Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Configuration du répertoire de travail
WORKDIR /var/www/html

# Copie des fichiers de configuration PHP
COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini

# Copie des fichiers de l'application
COPY . .

# Installation des dépendances Composer (avec packages de dev pour le développement)
RUN composer install --optimize-autoloader --no-interaction --ignore-platform-req=ext-mongodb

# Permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/var

# Exposition du port PHP-FPM
EXPOSE 9000

# Commande simple pour le développement
CMD ["php-fpm"]
