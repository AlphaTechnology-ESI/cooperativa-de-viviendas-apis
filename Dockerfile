FROM php:8.0-cli AS build

# Set working directory
WORKDIR /var/www/cooperativa-de-viviendas-apis

# Add docker php ext repo
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

# Install php extensions
RUN chmod +x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions mbstring pdo_mysql mysqli exif pcntl gd memcached

# Install dependencies
RUN apt-get update && apt-get install -y \
    locales \
    jpegoptim optipng pngquant gifsicle \
    git \
    curl \
    nano

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Add user for application
RUN groupadd -g 1000 www && useradd -u 1000 -ms /bin/bash -g www www

# Copy code to /var/www
COPY --chown=www:www-data . /var/www/cooperativa-de-viviendas-apis

# Start PHP built-in server via run.sh
CMD ["/var/www/cooperativa-de-viviendas-apis/docker/run.sh"]

EXPOSE 80
