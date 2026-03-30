# the different stages of this Dockerfile are meant to be built into separate images
# https://docs.docker.com/develop/develop-images/multistage-build/#stop-at-a-specific-build-stage
# https://docs.docker.com/compose/compose-file/#target


# https://docs.docker.com/engine/reference/builder/#understand-how-arg-and-from-interact
ARG PHP_VERSION=8.4
ARG FRANKENPHP_VERSION=1.11


# "php" stage
FROM dunglas/frankenphp:${FRANKENPHP_VERSION}-php${PHP_VERSION}-alpine AS bewelcome_php

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

RUN set -eux; \
	install-php-extensions \
		apcu \
		intl \
		gd \
		mysqli \
		pcntl \
		pdo_mysql \
		xsl \
		zip \
		exif \
		opcache \
	;

# https://github.com/nodejs/docker-node/issues/1126
RUN set -eux; \
	echo "@edge http://nl.alpinelinux.org/alpine/edge/main" >> /etc/apk/repositories; \
	apk add --no-cache yarn@edge

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN export PATH="/usr/local/bin:$PATH"

COPY docker/php/conf.d/bewelcome.prod.ini $PHP_INI_DIR/conf.d/bewelcome.ini

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1

# install Symfony Flex globally to speed up download of Composer packages (parallelized prefetching)
RUN set -eux; \
    composer global config --no-plugins allow-plugins.symfony/flex true; \
    composer global require "symfony/flex" --prefer-dist --no-progress --classmap-authoritative; \
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
COPY docker docker/
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
#COPY package.json yarn.lock webpack.config.js postcss.config.js tailwind.config.js tsconfig.json ./
#RUN set -eux; \
#	yarn install --frozen-lock; \
#	yarn encore production --mode=production

# do not use .env files in production
COPY .env ./
RUN composer dump-env prod; \
	rm .env

RUN set -eux; \
	mkdir -p var/cache var/log data/user/avatars data/gallery/member upload/images; \
	composer dump-autoload --classmap-authoritative --no-dev; \
	chmod +x bin/console; sync
VOLUME /srv/bewelcome/var
VOLUME /srv/bewelcome/data

COPY docker/frankenphp/Caddyfile /etc/caddy/Caddyfile

HEALTHCHECK --interval=10s --timeout=3s --retries=3 CMD ["frankenphp", "php-cli", "-r", "echo 1;"]

COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]


# "php" dev stage
# depends on the "php" stage above
FROM bewelcome_php AS bewelcome_php_dev

# build for production
ARG NODE_ENV=production

RUN set -eux; \
	apk add --no-cache \
		make \
		mysql-client; \
	chmod a+w $PHP_INI_DIR; \
	chmod -R a+w /srv/bewelcome/vendor
