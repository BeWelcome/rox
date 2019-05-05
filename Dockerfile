FROM alpine:3.9

# Install server dependencies.
RUN apk update
RUN apk add "php7<7.3"
RUN apk add unzip npm git make curl mysql-client \
            php7-json php7-phar php7-pdo_mysql php7-gd php7-pecl-xdebug php7-tokenizer php7-fileinfo \
            php7-xml php7-iconv php7-mbstring php7-dom php7-xmlwriter php7-simplexml php7-zip php7-session \
            php7-pcntl php7-mysqli php7-posix

# Allow PHP short opening tag, disable PHP magic quotes, increase  PHP memory limit
RUN sed -i "s/short_open_tag = .*/short_open_tag = On/" /etc/php7/php.ini \
        && sed -i "s/magic_quotes_gpc = .*/magic_quotes_gpc = Off/" /etc/php7/php.ini\
        && sed -i "s/memory_limit = .*/memory_limit = 1024M/" /etc/php7/php.ini

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/bin/composer
