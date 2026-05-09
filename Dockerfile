# =============================================================================
# Dockerfile - Laravel 12 + React/Vite para Render.com
# =============================================================================
# Stack: PHP 8.3 + Apache + Node.js 20 (build) + SQLite
# =============================================================================

FROM php:8.4-apache

# --- 1. Dependencias del sistema ---
RUN apt-get update && apt-get install -y --no-install-recommends \
    git curl zip unzip libsqlite3-dev libzip-dev libpng-dev libjpeg-dev \
    libfreetype6-dev libonig-dev libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_sqlite zip gd mbstring bcmath opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# --- 2. Instalar Node.js 20 (para compilar Vite/React) ---
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# --- 3. Instalar Composer ---
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# --- 4. Configurar Apache ---
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && a2enmod rewrite headers

# --- 5. Copiar código fuente ---
WORKDIR /var/www/html
COPY . .

# --- 6. Instalar dependencias PHP (producción) ---
RUN composer install --no-dev --optimize-autoloader --no-interaction

# --- 7. Compilar assets frontend (Vite + React + Tailwind) ---
RUN npm ci && npm run build && rm -rf node_modules

# --- 8. Permisos para Laravel ---
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# --- 9. Crear script de inicio ---
RUN echo '#!/bin/bash\n\
set -e\n\
\n\
# Configurar puerto dinámico para Apache\n\
sed -i "s/80/${PORT}/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf\n\
\n\
# Crear SQLite si no existe\n\
touch /var/www/html/database/database.sqlite\n\
chown www-data:www-data /var/www/html/database/database.sqlite\n\
\n\
# Generar APP_KEY si no existe\n\
if [ -z "$APP_KEY" ]; then\n\
  php artisan key:generate --force\n\
fi\n\
\n\
# Cachear configuración para producción\n\
php artisan config:cache\n\
php artisan route:cache\n\
php artisan view:cache\n\
\n\
# Migrar y sembrar base de datos\n\
php artisan migrate --force --seed\n\
\n\
# Iniciar Apache\n\
apache2-foreground\n\
' > /usr/local/bin/start.sh && chmod +x /usr/local/bin/start.sh

# --- 10. Arranque ---
CMD ["/usr/local/bin/start.sh"]
