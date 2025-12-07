FROM php:8.2-apache

WORKDIR /var/www/html

COPY try_apply_updates_and_run_apache2.sh /root/try_apply_updates_and_run_apache2.sh

RUN apt update && apt install --yes libzip-dev libpng-dev libfreetype-dev libjpeg62-turbo-dev libjpeg-dev

RUN docker-php-ext-configure gd --with-jpeg --with-freetype

RUN docker-php-ext-install pdo_mysql zip gd
RUN a2enmod rewrite # Example: Enable Apache module

EXPOSE 80

CMD ["/root/try_apply_updates_and_run_apache2.sh"]