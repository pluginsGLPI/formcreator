#!/bin/bash

# We need to install dependencies only for Docker
[[ ! -e /.dockerenv ]] && exit 0

set -e

# Install tools
apt-get update -yqq
apt-get install -yqq \
npm \
default-mysql-client \
git \
zip \
zlib1g-dev \
libpng-dev \
libicu-dev \
bzip2 \
libbz2-dev \
libzip-dev \
libjpeg-dev \
libfreetype6-dev \
gettext

# Install mysql driver
# Here you can install any other extension that you need
docker-php-ext-configure gd \
--enable-gd \
--with-freetype \
--with-jpeg

docker-php-ext-install gd intl mysqli bz2 zip
