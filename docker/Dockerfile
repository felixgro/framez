# Stage 1: Build dependencies and assets
FROM node:20-bullseye AS builder

# Install PHP, Composer, zip, and tools
RUN apt-get update \
    && apt-get install -y \
        php-cli \
        php-zip \
        php-mbstring \
        php-json \
        php-curl \
        zip \
        git \
        curl \
        bash \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Prepare the build process in the /app directory
WORKDIR /app

# Copy all necessary files into the /app directory
COPY app/ ./app/
COPY resources/ ./resources/
COPY LICENSE.txt .
COPY README.md .
COPY framez.php .
COPY globals.php .
COPY package.json .
COPY package-lock.json .
COPY composer.json .
COPY composer.lock .
COPY vite.config.js .
COPY plugin-info.json .

# Install NPM dependencies
RUN npm install

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader

# Generate a fresh build
RUN npm run build

# Remove development dependencies from node_modules
RUN npm prune --omit=dev

# Zip the plugin files
RUN mkdir -p /build
RUN zip -r /build/framez.zip . \
    -x "plugin-info.json" \
    -x "*.env"

# Perform the docusaurus build
COPY documentation/ ./documentation/
WORKDIR /app/documentation
RUN npm ci && npm run build
WORKDIR /app

    



# Stage 2: Minimal HTTP server for hosting zip and plugin-info.json
FROM debian:bullseye-slim

RUN apt-get update \
    && apt-get install -y busybox \
    && rm -rf /var/lib/apt/lists/*

# Make sur the /srv/http directory exists
# RUN mkdir -p /srv/http

# Create .mime.types file directly in the image
# RUN echo 'image/svg+xml    svg svgz' > /srv/http/.mime.types

# Copy built artifacts from builder stage
COPY --from=builder /build/framez.zip /srv/http/framez.zip
COPY --from=builder /app/plugin-info.json /srv/http/plugin-info.json
COPY --from=builder /app/documentation/build /srv/http

EXPOSE 3000

# Use the .mime.types file for BusyBox httpd
CMD ["busybox", "httpd", "-f", "-p", "3000", "-h", "/srv/http", "-m", "/srv/http/.mime.types"]