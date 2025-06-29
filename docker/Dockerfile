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

# Copy all files to container
COPY . .

# Install Node dependencies
RUN npm install

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader

# Build plugin assets
RUN npm run build

# Create build output directory and zip plugin files
RUN mkdir -p /build && zip -r /build/my-plugin.zip .

# Stage 2: Minimal HTTP server for hosting zip and plugin-info.json
FROM debian:bullseye-slim

RUN apt-get update \
    && apt-get install -y busybox \
    && rm -rf /var/lib/apt/lists/*

# Copy built artifacts from builder stage
COPY --from=builder /build/my-plugin.zip /srv/http/my-plugin.zip
COPY --from=builder /app/plugin-info.json /srv/http/plugin-info.json

EXPOSE 80

CMD ["busybox", "httpd", "-f", "-p", "80", "-h", "/srv/http"]
