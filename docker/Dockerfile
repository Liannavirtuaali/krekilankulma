FROM php:8.2-apache

# Asenna järjestelmäriippuvuudet
RUN apt-get update && apt-get install -y \
    # GD: JPEG, PNG, WebP, FreeType (kuten Altervistalla)
    libpng-dev libjpeg-dev libwebp-dev libfreetype6-dev \
    # intl-laajennus (ICU, kuten Altervistalla)
    libicu-dev \
    # gmp-laajennus (kuten Altervistalla)
    libgmp-dev \
    # sodium (kuten Altervistalla)
    libsodium-dev \
    # ImageMagick (kuten Altervistalla: imagick 3.x)
    libmagickwand-dev imagemagick \
    # gettext-laajennus
    gettext \
    && rm -rf /var/lib/apt/lists/*

# Konfiguroi GD ja asenna PHP-laajennukset
RUN docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mysqli \
        gd \
        bcmath \
        intl \
        gettext \
        gmp \
        exif

# sodium on PHP 8.x:ssä sisäänrakennettu — pelkkä enable riittää
RUN docker-php-ext-enable sodium

# imagick PECL-kautta
RUN pecl install imagick && docker-php-ext-enable imagick

# Ota käyttöön mod_rewrite (tarvitaan .htaccess:lle)
RUN a2enmod rewrite

# Salli .htaccess-tiedostot (AllowOverride All)
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

EXPOSE 80
