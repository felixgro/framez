# Stage 1: build dependencies and assets
FROM node:18-alpine as builder

# Install Composer
RUN apk add --no-cache curl php81 php81-phar php81-openssl php81-json php81-mbstring php81-tokenizer git unzip bash \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

# Copy everything
COPY . .

# Install Node and Composer dependencies
RUN npm install
RUN composer install --no-dev --optimize-autoloader

# Build your plugin assets
RUN npm run build

# Create zip archive of your plugin code
RUN apk add zip
RUN zip -r /build/my-plugin.zip src

# Stage 2: runtime container serving static files
FROM alpine:latest

# Install a minimal HTTP server (Caddy not needed if Coolify handles server)
RUN apk add --no-cache busybox-extras

# Copy build artifacts
COPY --from=builder /build/my-plugin.zip /srv/http/my-plugin.zip
COPY --from=builder /app/plugin-info.json /srv/http/plugin-info.json

# Expose port (Coolify usually does this automatically)
EXPOSE 80

# Serve the files via simple HTTP (Coolify will typically reverse proxy to it)
CMD ["httpd", "-f", "-p", "80", "-h", "/srv/http"]