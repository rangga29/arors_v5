FROM php:8.3-apache

# ============================================
# System Dependencies
# ============================================
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libxslt1-dev \
    libzip-dev \
    libexif-dev \
    zip \
    unzip \
    default-mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && rm -rf /var/lib/apt/lists/*

# ============================================
# PHP Extensions (sesuai Laragon)
# ============================================
RUN docker-php-ext-install \
    pdo_mysql \
    mysqli \
    mbstring \
    exif \
    fileinfo \
    gd \
    xsl \
    zip \
    bcmath \
    opcache

# ============================================
# Apache Configuration
# ============================================
RUN a2enmod rewrite headers
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# ============================================
# Composer
# ============================================
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ============================================
# Node.js 20
# ============================================
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

# ============================================
# PHP Custom Config
# ============================================
COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini

# ============================================
# Apache VirtualHost
# ============================================
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# ============================================
# Working Directory
# ============================================
WORKDIR /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
