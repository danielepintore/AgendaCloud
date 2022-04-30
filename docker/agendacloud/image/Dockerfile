FROM php:8.1-apache
# copy sources files
COPY ./build /var/www/html
# install mysqli
RUN docker-php-ext-install mysqli
# change apache document root
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
# configure php.ini file
RUN sed -i 's/;date.timezone =/date.timezone = "Europe\/Rome"/g' $PHP_INI_DIR/php.ini-production
# set a production php.ini file
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
# install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
# install zip and unzip for composer
RUN apt-get update && apt-get install -y unzip zip
# run composer to install all dependencies
WORKDIR /var/www/html
RUN composer install
# install cron
RUN apt-get -y install cron
# set up crontabs
RUN crontab -l | { cat; echo '*/5 * * * * /bin/bash -c "/usr/local/bin/php /var/www/html/resources/send_email_cron.php >/dev/null 2>&1"'; } | crontab -
RUN crontab -l | { cat; echo '*/2 * * * * /bin/bash -c "/usr/local/bin/php /var/www/html/resources/remove_expired_sessions.php >/dev/null 2>&1"'; } | crontab -
WORKDIR /
COPY ./entrypoint.sh /
RUN chmod +x /entrypoint.sh
ENTRYPOINT /entrypoint.sh