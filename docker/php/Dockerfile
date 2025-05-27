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
    && rm -rf /var/lib/apt/lists/*

# PHP 拡張機能をインストール
RUN docker-php-ext-install pdo_mysql

# PECL 経由で Redis 拡張をインストール
RUN pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /tmp/pear

# Node.js 16.x のインストール
RUN curl -sL https://deb.nodesource.com/setup_16.x | bash - && \
    apt-get install -y nodejs