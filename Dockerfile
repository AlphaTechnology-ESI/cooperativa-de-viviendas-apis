FROM php:8.0-cli AS build

# Establecer directorio de trabajo
WORKDIR /var/www/cooperativa-de-viviendas-apis

# Agregar repositorio de extensiones PHP para Docker
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

# Instalar extensiones PHP necesarias
RUN chmod +x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions mbstring pdo_mysql mysqli exif pcntl gd memcached

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    locales \
    jpegoptim optipng pngquant gifsicle \
    git \
    curl \
    nano

# Limpiar caché de apt
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Crear usuario para la aplicación
RUN groupadd -g 1000 www && useradd -u 1000 -ms /bin/bash -g www www

# Copiar código fuente al contenedor
COPY --chown=www:www-data . /var/www/cooperativa-de-viviendas-apis

# Iniciar servidor PHP integrado
CMD ["php", "-S", "0.0.0.0:80", "-t", "/var/www/cooperativa-de-viviendas-apis"]

EXPOSE 80
