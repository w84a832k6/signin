FROM php:8.4-fpm-alpine

# 安裝系統依賴
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    icu-dev \
    postgresql-dev \
    zip \
    unzip \
    mysql-client

# 配置和安裝 PHP 擴充功能
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        mysqli \
        gd \
        zip \
        exif \
        intl \
        opcache \
        bcmath

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

