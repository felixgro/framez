# Stage 1: Build dependencies and assets
FROM node:18-bullseye AS builder

# Install PHP and Composer
RUN apt-get update \
    && apt-get install -y \
        php-cli \
        php-zip \
        php-mbstring \
        php-json \
        php-curl \
        unzip \
        git \
        curl \
        bash \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set work directory
WORKDIR /app

# Copy everything
COPY . .

# Install Node dependencies
RUN npm install

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader

# Build plugin assets
RUN npm run build

# Create the zip archive
RUN zip -r /build/my-plugin.zip src

# Stage 2: Minimal HTTP server
FROM debian:bullseye-slim

# Install a minimal HTTP server (or use Caddy if you prefer)
RUN apt-get update \
    && apt-get install -y busybox \
    && rm -rf /var/lib/apt/lists/*

# Copy build artifacts
COPY --from=builder /build/my-plugin.zip /srv/http/my-plugin.zip
COPY --from=builder /app/plugin-info.json /srv/http/plugin-info.json

# Expose port
EXPOSE 80

# Start HTTP server
CMD ["busybox", "httpd", "-f", "-p", "80", "-h", "/srv/http"]