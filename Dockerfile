FROM php:8.2-cli

# Instala extensão FTP
RUN docker-php-ext-install ftp

WORKDIR /var/www/html/src

CMD ["php", "main.php"]