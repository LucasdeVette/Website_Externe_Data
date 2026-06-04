FROM php:8.2-apache

RUN apt-get update && apt-get install -y curl && \
    docker-php-ext-install pdo pdo_mysql && \
    a2enmod rewrite && \
    sed -i 's/DirectoryIndex index.php/DirectoryIndex index.html index.php/' /etc/apache2/conf-available/docker-php.conf && \
    sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

RUN groupadd -r appuser && useradd -r -g appuser -d /var/www/html -s /sbin/nologin appuser && \
    sed -i 's/APACHE_RUN_USER:=www-data/APACHE_RUN_USER:=appuser/' /etc/apache2/envvars && \
    sed -i 's/APACHE_RUN_GROUP:=www-data/APACHE_RUN_GROUP:=appuser/' /etc/apache2/envvars

COPY config/php.ini /usr/local/etc/php/conf.d/app.ini
COPY . /var/www/html/

RUN chown -R appuser:appuser /var/www/html && \
    chmod -R 755 /var/www/html

EXPOSE 80
