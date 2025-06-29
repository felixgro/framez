# Stage 1: Build dependencies and assets
FROM node:18-bullseye AS builder

# Install PHP, Composer, zip, and tools
RUN apt-get update \
    && apt-get install -y \
        php-cli \
        php-zip \
        php-mbstring \
        php-json \
        php-curl \
        unzip \
        zip \
        git \
        curl \
        bash \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

COPY app/ ./app/
COPY resources/ ./resources/
COPY framez.php .
COPY globals.php .
COPY package.json .
COPY package-lock.json .
COPY composer.json .
COPY composer.lock .

# Install Node dependencies
RUN npm install

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader

# Generate a fresh build
RUN npm run build

# Make the plugin zip file
RUN mkdir -p /build \
 && zip -r /build/framez.zip \
    framez.php
    
# Stage 2: Minimal HTTP server for hosting zip and plugin-info.json
FROM debian:bullseye-slim

RUN apt-get update \
    && apt-get install -y curl \
    && apt-get install -y busybox \
    && rm -rf /var/lib/apt/lists/*

# Copy built artifacts from builder stage
COPY --from=builder /build/framez.zip /srv/http/framez.zip
COPY --from=builder /app/plugin-info.json /srv/http/plugin-info.json

EXPOSE 3000

CMD ["busybox", "httpd", "-f", "-p", "3000", "-h", "/srv/http"]
