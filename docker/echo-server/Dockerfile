# Dockerfile for Laravel Echo Server
FROM node:18-alpine

WORKDIR /var/www/html

# package.json と package-lock.json (または yarn.lock) を先にコピーして、
# npm install のキャッシュを効率化
COPY app/package.json app/package-lock.json ./

# Laravel プロジェクトの node_modules をインストール
# Laravel Mix/Vite が動作するために必要
RUN npm install

# Laravel Echo Server をグローバルインストール
RUN npm install -g laravel-echo-server

# アプリケーションのコード全体をコピー
COPY app .

# WebSocket のポートを公開
EXPOSE 6001

# コンテナ起動時のデフォルトコマンド (docker-compose.yml で上書きされるが、念のため)
CMD ["laravel-echo-server", "start","--no-daemon"]