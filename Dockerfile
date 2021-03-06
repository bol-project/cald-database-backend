FROM php:5-apache

COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php.ini /usr/local/etc/php/php.ini

RUN mkdir /var/www/cald-database-backend
COPY . /var/www/cald-database-backend

RUN touch /var/www/cald-database-backend/public/.env
RUN chown www-data:www-data /var/www/cald-database-backend/logs
RUN docker-php-ext-install pdo pdo_mysql gettext

RUN a2enmod rewrite

EXPOSE 80
