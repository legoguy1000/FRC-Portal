FROM php:7.3-apache

WORKDIR /var/www/html/

# Update and install packages
RUN apt-get update && \
	apt-get install -y --no-install-recommends git libxml2-dev libzip-dev libcurl4-gnutls-dev unzip libgmp-dev && apt-get clean && rm -rf /var/lib/apt/lists/*

ADD https://dl-ssl.google.com/dl/linux/direct/mod-pagespeed-stable_current_amd64.deb /tmp/
RUN cd /tmp && dpkg -i mod-pagespeed-*.deb && apt-get install -f && rm mod-pagespeed-*.deb

# Install composer for PHP dependencies
RUN cd /tmp && curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

# Install php pre-reqs
RUN docker-php-ext-install mysqli
RUN docker-php-ext-install mbstring
RUN docker-php-ext-install xml
RUN docker-php-ext-install zip
RUN docker-php-ext-install curl
RUN docker-php-ext-install json
RUN docker-php-ext-install pdo
#RUN docker-php-ext-install pdo_dblib
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install gmp
RUN docker-php-ext-install intl

# Copy source over
COPY . /var/www/html/

# Install composer deps
RUN cd /var/www/html/api/app && composer install && composer dump-autoload

# Modify config
RUN cp /var/www/html/api/app/secured/config.example.ini /var/www/html/api/app/secured/config.ini

# Apache Config
COPY .docker/apache-default.conf /etc/apache2/sites-available/000-default.conf
RUN mkdir /ssl_certs

# Configure the server
#HEALTHCHECK --timeout=5m --start-period=15s \
VOLUME ["/var/www/html/api/app/secured","/var/www/html/favicons","/ssl_certs"]

# Enable apache mods
RUN a2enmod rewrite ssl headers deflate pagespeed
RUN a2ensite 000-default.conf

EXPOSE 80 443

COPY .docker/entrypoint /usr/local/bin/docker-php-entrypoint
RUN chmod +x /usr/local/bin/docker-php-entrypoint

ENTRYPOINT ["docker-php-entrypoint"]
CMD ["apache2-foreground"]
