# the different stages of this Dockerfile are meant to be built into separate images
# https://docs.docker.com/develop/develop-images/multistage-build/#stop-at-a-specific-build-stage
# https://docs.docker.com/compose/compose-file/#target


# https://docs.docker.com/engine/reference/builder/#understand-how-arg-and-from-interact
ARG PHP_VERSION=7.4.12
ARG NGINX_VERSION=1.17


# "php" stage
FROM php:${PHP_VERSION}-fpm-alpine AS bewelcome_php

# persistent / runtime deps
RUN apk add --no-cache \
		acl \
		freetype \
		libjpeg-turbo \
		libpng \
		fcgi \
		file \
		gettext \
		git \
		openssh-client \
		python3 \
	;

ARG APCU_VERSION=5.1.18
RUN set -eux; \
	apk add --no-cache --virtual .build-deps \
		$PHPIZE_DEPS \
		freetype-dev \
		icu-dev \
		libjpeg-turbo-dev \
		libpng-dev \
		libxslt-dev \
		libzip-dev \
		zlib-dev \
	; \
	\
	docker-php-ext-configure zip; \
	docker-php-ext-configure gd; \
	docker-php-ext-install -j$(nproc) \
		intl \
		gd \
		mysqli \
		pcntl \
		pdo_mysql \
		xmlrpc \
		xsl \
		zip \
	; \
	pecl install \
		apcu-${APCU_VERSION} \
	; \
	pecl clear-cache; \
	docker-php-ext-enable \
		apcu \
		opcache \
	; \
	\
	runDeps="$( \
		scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
			| tr ',' '\n' \
			| sort -u \
			| awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
	)"; \
	apk add --no-cache --virtual .phpexts-rundeps $runDeps; \
	\
	apk del .build-deps

# https://github.com/nodejs/docker-node/issues/1126
RUN set -eux; \
	echo "@edge http://nl.alpinelinux.org/alpine/edge/main" >> /etc/apk/repositories; \
	apk add --no-cache yarn@edge

COPY --from=composer:1 /usr/bin/composer /usr/bin/composer

RUN ln -s $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini
COPY docker/php/conf.d/bewelcome.prod.ini $PHP_INI_DIR/conf.d/bewelcome.ini

RUN set -eux; \
	{ \
		echo '[www]'; \
		echo 'ping.path = /ping'; \
	} | tee /usr/local/etc/php-fpm.d/docker-healthcheck.conf

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1
# install Symfony Flex globally to speed up download of Composer packages (parallelized prefetching)
RUN set -eux; \
	composer global require "symfony/flex" --prefer-dist --no-progress --no-suggest --classmap-authoritative; \
	composer clear-cache
ENV PATH="${PATH}:/root/.composer/vendor/bin"

WORKDIR /srv/bewelcome

# build for production
ARG APP_ENV=prod

# copy only specifically what we need for production
COPY assets assets/
COPY bin bin/
COPY build build/
COPY config config/
COPY lib lib/
COPY Mike42 Mike42/
COPY modules modules/
COPY pthacks pthacks/
COPY public public/
COPY roxlauncher roxlauncher/
COPY src src/
COPY templates templates/
COPY tools tools/
COPY translations translations/
COPY routes.php ./
COPY rox_docker.ini /srv/bewelcome/rox_local.ini

# prevent the reinstallation of vendors at every changes in the source code
COPY composer.json composer.lock symfony.lock ./
RUN set -eux; \
	composer install --prefer-dist --no-dev --no-scripts --no-progress --no-suggest; \
	composer clear-cache

# prevent the reinstallation of node_modules at every changes in the source code
COPY package.json yarn.lock webpack.config.js ./
RUN set -eux; \
	yarn install --frozen-lock; \
	yarn encore production --mode=production

# do not use .env files in production
COPY .env ./
RUN composer dump-env prod; \
	rm .env

RUN set -eux; \
	mkdir -p var/cache var/log; \
	composer dump-autoload --classmap-authoritative --no-dev; \
	chmod +x bin/console; sync
VOLUME /srv/bewelcome/var
VOLUME /srv/bewelcome/data

COPY docker/php/docker-healthcheck.sh /usr/local/bin/docker-healthcheck
RUN chmod +x /usr/local/bin/docker-healthcheck

HEALTHCHECK --interval=10s --timeout=3s --retries=3 CMD ["docker-healthcheck"]

COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]


# "nginx" stage
# depends on the "php" stage above
FROM nginx:${NGINX_VERSION}-alpine AS bewelcome_nginx

COPY docker/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf

WORKDIR /srv/bewelcome/public

COPY --from=bewelcome_php /srv/bewelcome/public ./


# "php" dev stage
# depends on the "php" stage above
FROM bewelcome_php AS bewelcome_php_dev

# build for production
ARG NODE_ENV=production

RUN set -eux; \
	apk add --no-cache \
		make \
		mysql-client
