# 使用イメージの指定
FROM php:8.1-fpm

# composer イメージ内から特定のファイルをコピー
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# apt-getコマンドのアップデートと必要なライブラリやドライバのインストール
RUN apt-get update && \
    apt-get install -y \
    git \
    zip unzip \
    curl \
    gnupg \
    build-essential \
    libssl-dev \
    libpng-dev\
    libjpeg-dev\
    libfreetype6-dev \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# OPcacheを有効化
RUN docker-php-ext-install opcache

# PHP 拡張機能をインストール
RUN docker-php-ext-install pdo_mysql gd zip && \
    docker-php-ext-enable gd
# PECL 経由で Redis 拡張をインストール
RUN pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /tmp/pear

# Node.js 16.x のインストール
RUN curl -sL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get install -y nodejs

WORKDIR /var/www/html

# composer.json だけをコピー（composer.lock は一旦無視）
COPY app/composer.json ./
# composer install はDockerfileでは実行せず、後で手動で実行する

# 残りのアプリケーションファイルをコピー
COPY app .