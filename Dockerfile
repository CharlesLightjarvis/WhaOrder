# =============================================================================
# Stage 1 — PHP + Composer dependencies
# =============================================================================
FROM php:8.4-cli-alpine AS composer-deps

RUN apk add --no-cache \
        git curl zip unzip \
        libpng-dev libjpeg-turbo-dev freetype-dev \
        icu-dev libzip-dev oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        bcmath exif gd intl mbstring pcntl pdo pdo_mysql zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Install dependencies before copying source (layer cache optimization)
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-interaction \
        --optimize-autoloader \
        --prefer-dist

COPY . .

# Discover packages (|| true — APP_KEY not needed at this stage)
RUN php artisan package:discover --ansi

# =============================================================================
# Stage 2 — Node.js asset + SSR bundle build
# =============================================================================
# Reuse composer-deps: PHP 8.4 + extensions + vendor + artisan already present.
# Wayfinder runs `php artisan wayfinder:generate` during the Vite build.
FROM composer-deps AS node-build

RUN apk add --no-cache nodejs npm

ENV NODE_OPTIONS="--max-old-space-size=1536"
ENV PUPPETEER_SKIP_DOWNLOAD=true

RUN npm ci --prefer-offline

# Builds both client bundle (public/build) and SSR bundle (bootstrap/ssr)
RUN npm run build:ssr

# =============================================================================
# Stage 3 — Production image (PHP-FPM + Nginx + Supervisor)
# =============================================================================
FROM php:8.4-fpm-alpine AS production

LABEL maintainer="pmindfull"

# Runtime system dependencies
RUN apk add --no-cache \
        nginx \
        supervisor \
        curl \
        chromium \
        nss \
        harfbuzz \
        ca-certificates \
        ttf-freefont \
        libstdc++ \
        libpng libjpeg-turbo freetype \
        icu-libs libzip oniguruma \
    && rm -rf /var/cache/apk/*

# PHP extensions (build deps removed after install to keep image lean)
RUN apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        libpng-dev libjpeg-turbo-dev freetype-dev \
        icu-dev libzip-dev oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        bcmath exif gd intl mbstring pcntl pdo pdo_mysql opcache zip \
    && pecl install redis-6.3.0 \
    && docker-php-ext-enable redis \
    && apk del .build-deps \
    && rm -rf /var/cache/apk/*

# Node.js needed at runtime by `php artisan inertia:start-ssr`
RUN apk add --no-cache nodejs npm

ENV PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium-browser
ENV LARAVEL_PDF_NO_SANDBOX=true

# PHP configuration
COPY docker/php/php.ini        "$PHP_INI_DIR/conf.d/99-app.ini"
COPY docker/php/opcache.ini    "$PHP_INI_DIR/conf.d/10-opcache.ini"
COPY docker/php/php-fpm.conf   /usr/local/etc/php-fpm.d/www.conf

# Nginx
COPY docker/nginx/nginx.conf   /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

# Supervisor
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf
COPY docker/supervisor/conf.d/          /etc/supervisor/conf.d/

WORKDIR /var/www/html

# Application source (copied in order of change frequency)
COPY --chown=www-data:www-data . .
COPY --from=composer-deps --chown=www-data:www-data /app/vendor        ./vendor
COPY --from=node-build    --chown=www-data:www-data /app/node_modules  ./node_modules
COPY --from=node-build    --chown=www-data:www-data /app/public/build  ./public/build
COPY --from=node-build    --chown=www-data:www-data /app/bootstrap/ssr ./bootstrap/ssr

# Ensure storage directories exist with correct permissions
RUN mkdir -p \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/testing \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 755 storage bootstrap/cache \
    && mkdir -p /run/nginx \
    && mkdir -p /var/lib/nginx/tmp/client_body \
    && chown -R www-data:www-data /var/lib/nginx \
    && chmod -R 755 /var/lib/nginx

COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=60s --retries=3 \
    CMD curl -fsS http://127.0.0.1/up > /dev/null || exit 1

CMD ["/usr/local/bin/start.sh"]
