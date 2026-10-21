# Mesma imagem para o site (Apache) e para o servidor WebSocket (CLI).
FROM composer:2 AS dependencias
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist --optimize-autoloader --no-scripts

FROM php:8.4-apache

RUN docker-php-ext-install pdo_mysql \
    && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && a2enmod rewrite \
    && sed -ri 's!/var/www/html!/var/www/app/public!g' /etc/apache2/sites-available/000-default.conf \
    && printf '<Directory /var/www/app/public>\n    AllowOverride All\n</Directory>\n' > /etc/apache2/conf-enabled/app.conf \
    && printf 'date.timezone=${TZ}\n' > /usr/local/etc/php/conf.d/timezone.ini

WORKDIR /var/www/app
COPY --from=dependencias /app/vendor ./vendor
COPY . .

EXPOSE 80 8090
