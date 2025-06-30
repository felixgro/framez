# Stage 1: Build dependencies and assets
FROM node:20-bullseye AS builder

# Install PHP, Composer, zip, and tools
RUN apt-get update \
    && apt-get install -y \
        php-cli \
        zip \
        git \
        curl \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Prepare the build process in the /plugin directory
WORKDIR /app
COPY plugin/ .

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

# Copy the plugin-info.json file to the build directory
COPY plugin-info.json /build/plugin-info.json

# Perform the docusaurus build
WORKDIR /documentation
COPY documentation/ .
RUN npm ci && npm run build
    



# Stage 2: Minimal HTTP server for hosting zip and plugin-info.json
FROM debian:bullseye-slim

RUN apt-get update \
    && apt-get install -y busybox curl \
    && rm -rf /var/lib/apt/lists/*

COPY --from=builder /build/framez.zip /srv/http/framez.zip
COPY --from=builder /build/plugin-info.json /srv/http/plugin-info.json
COPY --from=builder /documentation/build /srv/http

EXPOSE 3000

HEALTHCHECK --interval=30s --timeout=10s --start-period=5s \
    CMD curl -f http://localhost:3000 || exit 1

CMD ["busybox", "httpd", "-f", "-p", "3000", "-h", "/srv/http"]