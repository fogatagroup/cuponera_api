FROM php:7.2.2-fpm

# Copy composer.lock and composer.json
COPY composer.lock composer.json /app/api/

WORKDIR /app/api/

RUN apt-get update -y && apt-get install -y libmcrypt-dev openssl

RUN docker-php-ext-install pdo mcrypt mbstring

RUN apt-get update && apt-get install -y \
    build-essential \
    mysql-client \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl

RUN docker-php-ext-configure gd --with-gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ --with-png-dir=/usr/include/

RUN docker-php-ext-install gd

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY . /app/api

CMD cd /app/api && php artisan migrate --seed && php artisan key:generate
CMD cd /app/api && cp .env.example .env && php artisan serve --host=0.0.0.0 --port=8080

EXPOSE 8080
CMD ["php-fpm"]
