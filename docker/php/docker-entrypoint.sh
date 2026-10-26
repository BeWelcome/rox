#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- php-fpm "$@"
fi

ENTRYPOINT_SETUP_RAN=false

# Setup progress, for docker/php/setup-progress.sh (the devcontainer shows it in a terminal) and docker compose logs.
SETUP_STATUS=/tmp/bewelcome-setup.status
setup_step() {
	echo "==> setup: $1"
	echo "$(date +%s) $1" >> "$SETUP_STATUS"
}

if [ "$1" = 'php-fpm' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then
	PHP_INI_RECOMMENDED="$PHP_INI_DIR/php.ini-production"
	if [ "$APP_ENV" != 'prod' ]; then
		PHP_INI_RECOMMENDED="$PHP_INI_DIR/php.ini-development"
	fi
	ln -sf "$PHP_INI_RECOMMENDED" "$PHP_INI_DIR/php.ini"
	: > "$SETUP_STATUS"

	mkdir -p var/cache var/log data/user/avatars data/gallery/member upload/images
#	setfacl -R -m u:www-data:rwX -m u:"$(whoami)":rwX var build data upload
#	setfacl -dR -m u:www-data:rwX -m u:"$(whoami)":rwX var build data upload

	if [ "$APP_ENV" != 'prod' ] && [ -f /certs/localCA.crt ]; then
		ln -sf /certs/localCA.crt /usr/local/share/ca-certificates/localCA.crt
		update-ca-certificates
	fi

	if [ "$APP_ENV" != 'prod' ] && [ -f rox_docker.ini ]; then
		cp rox_docker.ini rox_local.ini
	fi

	if [ "$APP_ENV" != 'prod' ] && [ ! -f VERSION ]; then
	    git config --global --add safe.directory /srv/bewelcome
		git rev-parse --short HEAD > VERSION
	fi

	if [ "$APP_ENV" != 'prod' ]; then
		setup_step "Installing JavaScript and PHP dependencies (bun, composer)"
		bun i --frozen-lockfile
		composer install --prefer-dist --no-progress --no-suggest --no-interaction --no-scripts
	fi

	database_host=$(grep '^DB_HOST=' .env | cut -f 2 -d '=')
	database_port=$(grep '^DB_PORT=' .env | cut -f 2 -d '=')
	database_name=$(grep '^DB_NAME=' .env | cut -f 2 -d '=')
	database_user=$(grep '^DB_USER=' .env | cut -f 2 -d '=')
	database_password=$(grep '^DB_PASS=' .env | cut -f 2 -d '=')
	database_host="${database_host:-${DB_HOST:-db}}"
	database_port="${database_port:-${DB_PORT:-3306}}"
	database_name="${database_name:-${DB_NAME:-bewelcome}}"
	database_user="${database_user:-${DB_USER:-bewelcome}}"
	database_password="${database_password:-${DB_PASSWORD:-bewelcome}}"

	setup_step "Waiting for the database"
	db_wait_seconds=0
	db_wait_max="${BEWELCOME_DB_READY_MAX_WAIT_SECONDS:-300}"

	until php -r "new PDO('mysql:host=${database_host};port=${database_port};dbname=${database_name}', '${database_user}', '${database_password}');" > /dev/null 2>&1; do
		if [ "$db_wait_seconds" -ge "$db_wait_max" ]; then
			echo "Database not ready after ${db_wait_max}s; aborting." >&2
			exit 1
		fi
		sleep 1
		echo "Waited... ${db_wait_seconds}s."
		db_wait_seconds=$((db_wait_seconds + 1))
	done

	if [ "$APP_ENV" != 'prod' ]; then
		setup_step "Creating the development database and importing seed data"
		if ! bin/console test:database:create --drop --force --no-interaction; then
			setup_step "WARNING: database creation failed, see docker compose logs php"
			echo "=========================================================" >&2
			echo " ERROR: Database creation failed!" >&2
			echo " Skipping SQL imports to avoid swallowing real errors." >&2
			echo " Please fix the schema and run bin/console test:database:create manually." >&2
			echo "=========================================================" >&2
		else
			if [ -f docker/db/word.sql ]; then
				echo "Importing translations"
				mysql $database_name -u $database_user -p$database_password -h $database_host --port=$database_port < docker/db/word.sql || echo "ERROR: Failed to import word.sql" >&2
			fi
		fi
	fi

	# Clear the cache in prod so a stale compiled container from a previous image
	# (var/ volume) can't crash the console commands below. Doctrine migrations are
	# not run here: they are run by hand after the deploy (see docker/DEPLOYMENT.md).
	if [ "$APP_ENV" = 'prod' ]; then
		setup_step "Clearing the cache"
		bin/console cache:clear
	fi

	setup_step "Importing missing translations and warming up the cache"
	# Needs the current schema. When a deploy brings migrations, they run after the
	# container is up, so a failure here must not stop the start (see docker/DEPLOYMENT.md).
	if ! bin/console translations:add:missing; then
		echo "WARNING: translations:add:missing failed. Run it again after the migrations." >&2
	fi
	
	# WarmUp translations now database is up to date
	composer run-script --no-dev post-install-cmd

	# cache:clear runs as root; fix ownership so www-data can write at runtime
	chown -R www-data:www-data var/cache var/log

	# Local development only: on the servers the deployment creates the data directories
	# (avatars, gallery, uploads) with the right owner. Locally /srv/bewelcome/data is a
	# Docker volume that this script and the fixtures fill as root, so php-fpm (www-data)
	# can't write resized avatars or new uploads there.
	if [ "$APP_ENV" != 'prod' ]; then
		mkdir -p data/user/avatars data/gallery data/uploaded data/groups
		chown -R www-data:www-data data
	fi

	if [ "$APP_ENV" != 'prod' ]; then
		setup_step "Building the frontend assets (bun encore dev)"
		bun encore dev --mode=development
	fi

	setup_step "ready"
	ENTRYPOINT_SETUP_RAN=true
fi

# Non-php-fpm containers (cron, workers) skip the block above and receive no
# cache:clear or warmup, leaving them vulnerable to stale anonymous-volume
# content. Warm explicitly so every container type starts with a compiled
# cache that reflects the current image's templates.
if [ "$APP_ENV" = 'prod' ] && [ "$ENTRYPOINT_SETUP_RAN" = 'false' ]; then
	php bin/console cache:warmup --env=prod --no-debug
fi

exec docker-php-entrypoint "$@"
