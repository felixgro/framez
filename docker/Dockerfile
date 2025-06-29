# Stage 1: Build dependencies and assets
FROM node:18-alpine AS builder

# Install Composer and PHP
RUN apk add --no-cache \
    curl \
    php8 \
    php8-phar \
    php8-openssl \
    php8-json \
    php8-mbstring \
    php8-tokenizer \
    git \
    unzip \
    bash \
    && curl -sS https://getcomposer.org/installer | php8 -- --install-dir=/usr/local/bin --filename=composer

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

# Create the zip archive of your plugin folder (assuming your plugin code is in ./src)
RUN apk add zip
RUN zip -r /build/my-plugin.zip src

# Stage 2: Minimal HTTP server
FROM alpine:latest

# Install a minimal HTTP server
RUN apk add --no-cache busybox-extras

# Copy build artifacts
COPY --from=builder /build/my-plugin.zip /srv/http/my-plugin.zip
COPY --from=builder /app/plugin-info.json /srv/http/plugin-info.json

# Expose port 80
EXPOSE 80

# Start HTTP server (Coolify will reverse proxy it)
CMD ["httpd", "-f", "-p", "80", "-h", "/srv/http"]
