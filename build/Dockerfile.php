FROM php:8.2-apache
ARG UID
ARG GID

RUN apt-get update && apt-get install -y unzip git curl libzip-dev \
    && docker-php-ext-install mysqli zip pdo pdo_mysql \
    && a2enmod rewrite headers

RUN sed -ri -e 's/^([ \t]*)(<\/VirtualHost>)/\1\tHeader set Access-Control-Allow-Headers "Content-Type"\n\1\2/g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's/^([ \t]*)(<\/VirtualHost>)/\1\tHeader set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"\n\1\2/g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's/^([ \t]*)(<\/VirtualHost>)/\1\tHeader set Access-Control-Allow-Origin "*"\n\1\2/g' /etc/apache2/sites-available/*.conf

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN groupadd -g $GID informatica && \
    useradd -u $UID -g informatica -m informatica

ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

RUN chown -R informatica:informatica /var/www

WORKDIR /var/www/html

USER informatica
CMD ["apache2-foreground"]