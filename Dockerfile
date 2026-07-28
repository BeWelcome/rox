# the different stages of this Dockerfile are meant to be built into separate images
# https://docs.docker.com/engine/reference/builder/#understand-how-arg-and-from-interact
ARG PHP_VERSION=8.4
ARG FRANKENPHP_VERSION=1.11


# Shared PHP/FrankenPHP runtime base.
FROM dunglas/frankenphp:${FRANKENPHP_VERSION}-php${PHP_VERSION}-alpine AS bewelcome_php_base

RUN apk add --no-cache \
		acl \
		freetype \
		libjpeg-turbo \
		libpng \
		fcgi \
		gettext \
	;

RUN set -eux; \
	install-php-extensions \
		apcu \
		curl \
		gmp \
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

COPY docker/php/conf.d/bewelcome.prod.ini $PHP_INI_DIR/conf.d/bewelcome.ini

RUN set -eux; \
	ln -sf "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"; \
	sed -i -e "s/^ *memory_limit.*/memory_limit = 4G/g" "$PHP_INI_DIR/php.ini"

WORKDIR /srv/bewelcome


# Source snapshot reused by build and development stages.
FROM bewelcome_php_base AS bewelcome_source

COPY assets assets/
COPY bin bin/
COPY build build/
COPY config config/
COPY docker docker/
COPY lib lib/
COPY Migrations Migrations/
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
COPY composer.json composer.lock symfony.lock ./
COPY package.json bun.lock webpack.config.js postcss.config.js tailwind.config.js tsconfig.json ./
COPY .env ./


# Build PHP dependencies and optimized autoload outside the final runtime.
FROM bewelcome_php_base AS bewelcome_vendor_deps

RUN set -eux; \
	apk add --no-cache curl; \
	curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY composer.json composer.lock symfony.lock ./

RUN set -eux; \
	COMPOSER_ALLOW_SUPERUSER=1 composer install --prefer-dist --no-dev --no-scripts --no-progress --no-autoloader; \
	composer clear-cache

FROM bewelcome_vendor_deps AS bewelcome_vendor

COPY --from=bewelcome_source /srv/bewelcome ./

RUN set -eux; \
	COMPOSER_ALLOW_SUPERUSER=1 composer dump-autoload --classmap-authoritative --no-dev; \
	COMPOSER_ALLOW_SUPERUSER=1 composer dump-env prod; \
	composer clear-cache; \
	rm -rf /root/.composer /root/.cache/composer


# Build frontend assets outside the final runtime.
FROM bewelcome_php_base AS bewelcome_bun

RUN set -eux; \
	apk add --no-cache bash curl git openssh-client unzip; \
	curl -fsSL https://bun.sh/install -o bun-install.sh; \
	bash bun-install.sh; \
	ln -s /root/.bun/bin/bun /usr/local/bin/bun; \
	rm bun-install.sh

FROM bewelcome_bun AS bewelcome_assets

COPY package.json bun.lock ./
COPY --from=bewelcome_vendor_deps /srv/bewelcome/vendor vendor/

RUN set -eux; \
	bun i --frozen-lockfile

COPY --from=bewelcome_source /srv/bewelcome ./

# Stub var/translations so encore can compile (entrypoint cache warmup fills real translations at runtime).
RUN mkdir -p var/translations && printf '%s\n' \
	'// auto-generated stub for image build' \
	'export const localeFallbacks = {"en":"en"};' \
	'export const messages = {};' \
	> var/translations/index.js

RUN set -eux; \
	bun encore production --mode=production


# Self-contained production image.
FROM bewelcome_php_base AS bewelcome_php

ARG APP_VERSION=unknown
ARG APP_VERSION_TIMESTAMP=

ENV APP_ENV=prod

COPY --from=bewelcome_source /srv/bewelcome/bin bin/
COPY --from=bewelcome_source /srv/bewelcome/build build/
COPY --from=bewelcome_source /srv/bewelcome/config config/
COPY --from=bewelcome_source /srv/bewelcome/lib lib/
COPY --from=bewelcome_source /srv/bewelcome/Migrations Migrations/
COPY --from=bewelcome_source /srv/bewelcome/Mike42 Mike42/
COPY --from=bewelcome_source /srv/bewelcome/modules modules/
COPY --from=bewelcome_source /srv/bewelcome/pthacks pthacks/
COPY --from=bewelcome_source /srv/bewelcome/public public/
COPY --from=bewelcome_source /srv/bewelcome/roxlauncher roxlauncher/
COPY --from=bewelcome_source /srv/bewelcome/src src/
COPY --from=bewelcome_source /srv/bewelcome/templates templates/
COPY --from=bewelcome_source /srv/bewelcome/tools tools/
COPY --from=bewelcome_source /srv/bewelcome/translations translations/
COPY --from=bewelcome_source /srv/bewelcome/routes.php ./
COPY --from=bewelcome_source /srv/bewelcome/rox_local.ini ./
COPY --from=bewelcome_source /srv/bewelcome/composer.json ./
COPY --from=bewelcome_vendor /srv/bewelcome/vendor vendor/
COPY --from=bewelcome_vendor /srv/bewelcome/.env.local.php ./
COPY --from=bewelcome_assets /srv/bewelcome/public/build public/build/
COPY --from=bewelcome_assets /srv/bewelcome/public/main.js /srv/bewelcome/public/service-worker.js public/

RUN set -eux; \
	printf '%s\n' "$APP_VERSION" > VERSION; \
	if [ -n "$APP_VERSION_TIMESTAMP" ]; then php -r 'touch("VERSION", (int) $argv[1]);' "$APP_VERSION_TIMESTAMP"; fi; \
	mkdir -p var/cache var/log data/user/avatars data/gallery/member upload/images public/bundles /config /data; \
	find public -name '*.map' -type f -delete; \
	chown -R www-data:www-data var data upload public/bundles /config /data; \
	chmod +x bin/console; \
	apk upgrade --no-cache; \
	sync
VOLUME /srv/bewelcome/var
VOLUME /srv/bewelcome/data

COPY docker/frankenphp/Caddyfile /etc/caddy/Caddyfile
COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

USER www-data

HEALTHCHECK --interval=10s --timeout=3s --retries=3 CMD ["frankenphp", "php-cli", "-r", "echo 1;"]

ENTRYPOINT ["docker-entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]


# Local development image. The repository is bind-mounted over /srv/bewelcome.
FROM bewelcome_source AS bewelcome_php_dev

ENV APP_ENV=dev
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN set -eux; \
	apk add --no-cache bash curl git make mariadb-client openssh-client python3 unzip; \
	curl -fsSL https://bun.sh/install -o bun-install.sh; \
	bash bun-install.sh; \
	ln -s /root/.bun/bin/bun /usr/local/bin/bun; \
	rm bun-install.sh; \
	curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer; \
	chmod a+w "$PHP_INI_DIR"; \
	mkdir -p vendor var/cache var/log; \
	chmod -R a+w vendor; \
	chmod -R 777 var

COPY docker/frankenphp/Caddyfile /etc/caddy/Caddyfile
COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

HEALTHCHECK --interval=10s --timeout=3s --retries=3 CMD ["frankenphp", "php-cli", "-r", "echo 1;"]

ENTRYPOINT ["docker-entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
