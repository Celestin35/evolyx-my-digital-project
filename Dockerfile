FROM php:8.4-cli

WORKDIR /var/www/html

# Install les outils Linux
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl \
        git \
        unzip \
        libzip-dev \
    # Installe les extensions PHP
    && docker-php-ext-install pdo_mysql pdo_sqlite zip \
     # Installe Node 22
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    # Nettoie les fichiers temporaires -> Image légère
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
# Expose potentiellement les ports pour Laravel et Vite 
EXPOSE 8000 5173

# Supprime public/hot (utilisé en mode dev) et lance le serveur
CMD ["sh", "-c", "rm -f public/hot && php artisan serve --host=0.0.0.0 --port=8000"]
