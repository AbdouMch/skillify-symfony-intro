FROM php:8.3-apache

ARG DEBIAN_FRONTEND=noninteractive

# Update
RUN apt-get -y update --fix-missing && \
    apt-get upgrade -y && \
    apt-get --no-install-recommends install -y apt-utils && \
    rm -rf /var/lib/apt/lists/*

# Install composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# install symfony cli
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash && \
    apt-get update && \
    apt-get install -y symfony-cli

# ajout mcrypt pour le bundle : ambta/doctrine-encrypt-bundle
RUN apt-get update && apt-get install -y libmcrypt-dev
RUN pecl install mcrypt && docker-php-ext-enable mcrypt

# Install useful tools and install important libaries
RUN apt-get -y update && \
    apt-get -y --no-install-recommends install iputils-ping nano wget libsqlite3-dev libsqlite3-0 && \
    apt-get -y --no-install-recommends install default-mysql-client zlib1g-dev libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libicu-dev && \
    apt-get -y --no-install-recommends install --fix-missing apt-utils build-essential git curl openssh-client libonig-dev && \
    apt-get -y --no-install-recommends install --fix-missing libcurl4 zip && \
    rm -rf /var/lib/apt/lists/*

# Install xdebug
RUN pecl install xdebug && \
    docker-php-ext-enable xdebug && \
    mkdir /var/log/xdebug && \
    mkdir -p /usr/local/etc/php/disabled && \
    mv /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini /usr/local/etc/php/disabled

# PHP Extensions
RUN docker-php-ext-install pdo_mysql && \
    docker-php-ext-install zip && \
    docker-php-ext-install exif && \
    docker-php-ext-install gd && \
    docker-php-ext-install gettext && \
    docker-php-ext-install intl && \
    docker-php-ext-install mysqli

# Install OPCACHE
RUN docker-php-ext-install opcache
COPY conf.d/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Ajout des fichiers de configs
COPY conf.d/apache2/sites-available/ /etc/apache2/sites-available/

# Activation des sites
RUN a2ensite localhost.conf

# Enable apache modules
RUN a2enmod rewrite headers

# Cleanup
RUN rm -rf /usr/src/*
