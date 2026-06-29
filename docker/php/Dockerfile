FROM php:8.4-fpm

RUN groupadd -g 1000 laravel && \
    useradd -u 1000 -g laravel -s /bin/sh -m laravel
    
RUN mkdir -p /var/www/html

RUN chown -R laravel:laravel /var/www/html

# Install system dependencies
RUN apt-get update -y \
    && apt-get upgrade -y \
    && apt-get install -y \
    git \
    curl \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    libxslt1-dev \
    zip \
    unzip \
    procps \
    supervisor \
    && rm -rf /var/lib/apt/lists/*

# Install Node.js via NodeSource (avoids Debian's bloated node packages)
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions using docker-php-ext-install
RUN docker-php-ext-install \
    zip \
    pdo_mysql \
    bcmath \
    intl \
    dom \
    xml \
    simplexml \
    xmlreader \
    xmlwriter \
    pcntl 

# Install PECL extensions
RUN pecl install excimer xdebug \
    && docker-php-ext-enable excimer xdebug

# By default remove the xdebug config file so it doesn't run and take resources
# If you want to run xdebug provide volume in docker-compose that will recreate a new one.
RUN rm /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Set up xdebug log
RUN touch /var/www/xdebug.log && chmod 775 /var/www/xdebug.log

COPY ./php/www.conf /usr/local/etc/php-fpm.d/www.conf

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN chown -R www-data:www-data /var/www/html

COPY ./php/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY ./php/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]