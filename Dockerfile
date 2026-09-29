# Imagen oficial de PHP 8.2 con Apache
FROM php:8.2-apache

# Evita avisos de ServerName en los logs
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Actualizar e instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    libonig-dev \
    zip \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# AQUÍ AGREGAMOS pdo Y pdo_mysql:
RUN docker-php-ext-install pdo pdo_mysql curl mbstring

# Habilitar el módulo rewrite de Apache
RUN a2enmod rewrite

# Copiar el código del proyecto
COPY . /var/www/html/

# Permisos
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80