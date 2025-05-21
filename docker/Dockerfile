FROM php:8.2-cli

# Instala extensão FTP
RUN docker-php-ext-install ftp

# Instala Xdebug para debug
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Configurações básicas do Xdebug para debug remoto
COPY ./xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

WORKDIR /var/www/html/src

CMD ["php", "main.php"]