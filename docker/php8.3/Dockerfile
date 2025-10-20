FROM php:8.3.26-cli

RUN apt-get update && apt-get install -y git-core zlib1g-dev libzip-dev zip unzip
RUN docker-php-ext-install zip

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php -r "if (hash_file('SHA384', 'composer-setup.php') === trim(file_get_contents('https://composer.github.io/installer.sig'))) { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" && \
    php composer-setup.php && \
    mv composer.phar /usr/local/bin/composer && \
    unlink composer-setup.php

WORKDIR /srv/www

CMD ["tail", "-f", "/dev/null"]
