FROM php:7.1-cli
RUN apt-get update && apt-get upgrade -y && docker-php-ext-install -j$(nproc) pdo_mysql
RUN apt-get install -y git bzip2 mysql-client zlib1g zlib1g-dev zlibc libicu-dev
RUN docker-php-ext-install -j$(nproc) pdo_mysql zip mbstring intl
RUN cd /tmp && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && php composer-setup.php && rm composer-setup.php && mv composer.phar /usr/bin/composer