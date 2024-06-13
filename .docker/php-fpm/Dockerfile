FROM php:8.1-fpm
LABEL mantainer="Gustav Margriff <margriff@protonmail.com>"

# Install "curl", "libmemcached-dev", "libpq-dev", "libjpeg-dev",
#         "libpng12-dev", "libfreetype6-dev", "libssl-dev", "libmcrypt-dev",
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        curl \
        git \
        libmemcached-dev \
        libz-dev \
        libpq-dev \
        libjpeg-dev \
        libpng-dev \
        libwebp-dev \
        libfreetype6-dev \
        libssl-dev \
        libmcrypt-dev \
        unzip \
        libgmp-dev \
        libzip-dev \
        zip

# Install the PHP mcrypt extention
RUN pecl install mcrypt-1.0.5 \
    && docker-php-ext-enable mcrypt.so
    
# Install the PHP pdo_mysql extention
RUN docker-php-ext-install mysqli pdo pdo_mysql exif

# Install the PHP soap extention
RUN rm /etc/apt/preferences.d/no-debian-php \
        && apt-get update -yqq --allow-releaseinfo-change \
        && apt-get -y install libxml2-dev php-soap \
        && docker-php-ext-install soap
    
# Install the PHP xdebug extention
RUN pecl install xdebug-3.1.5 \
    && docker-php-ext-enable xdebug

#####################################
# gd:
#####################################

# Install the PHP gd library
RUN docker-php-ext-configure gd --enable-gd --prefix=/usr --with-jpeg --with-freetype \
    && docker-php-ext-install -j$(nproc) gd

# Install the PHP zip extention
RUN docker-php-ext-install zip \
    && docker-php-ext-enable zip

RUN docker-php-ext-install gmp \
    && docker-php-ext-enable gmp

# Set user permission over folder
RUN chown -R www-data:www-data /var/www
RUN chmod 777 -R /var/www

# install composer
RUN curl -sS https://getcomposer.org/installer | php -- --filename=composer --install-dir=/bin
ENV PATH /root/.composer/vendor/bin:$PATH