FROM php:8.4-fpm-alpine

# 安裝系統依賴（包括編譯工具，稍後再清理）
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    icu-dev \
    sqlite-dev \
    zip \
    unzip \
    $PHPIZE_DEPS

# 配置和安裝 PHP 擴充功能
# 注意：pdo、pdo_sqlite、sqlite3、bcmath 和 exif 在 PHP 8.4 中已經內建，不需要單獨安裝
RUN set -eux; \
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-install -j$(nproc) gd zip intl opcache; \
    apk del --no-network $PHPIZE_DEPS

# 安裝 Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 設定工作目錄
WORKDIR /var/www/html

# 複製 PHP-FPM 配置
COPY php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# 設定權限
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# 切換到 www-data 用戶
USER www-data

# 暴露端口
EXPOSE 9000

CMD ["php-fpm"]

