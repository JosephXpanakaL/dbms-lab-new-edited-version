FROM php:8.2-apache
RUN docker-php-ext-install mysqli && a2enmod rewrite \
    && printf 'upload_max_filesize=6M\npost_max_size=7M\n' > /usr/local/etc/php/conf.d/uploads.ini
WORKDIR /var/www/html
COPY . /var/www/html/
RUN mkdir -p /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html/uploads
EXPOSE 80
