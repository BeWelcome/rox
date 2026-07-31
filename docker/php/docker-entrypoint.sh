#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- frankenphp run "$@"
fi

ensure_composer_dependencies() {
	if [ -f vendor/autoload_runtime.php ]; then
		return 0
	fi

	if [ "$APP_ENV" = 'prod' ]; then
		echo "Missing vendor/autoload_runtime.php in production image; rebuild the image instead of installing dependencies at runtime." >&2
		exit 1
	fi

	echo "Installing Composer dependencies (vendor/autoload_runtime.php missing)..."
	composer install --prefer-dist --no-progress --no-interaction --no-scripts
}

if [ "$1" = 'frankenphp' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ] || [ "$1" = 'ls' ]; then
	PHP_INI_RECOMMENDED="$PHP_INI_DIR/php.ini-production"
	if [ "$APP_ENV" != 'prod' ]; then
		PHP_INI_RECOMMENDED="$PHP_INI_DIR/php.ini-development"
	fi
	if [ -w "$PHP_INI_DIR" ]; then
		rm -f "$PHP_INI_DIR/php.ini"
		ln -s "$PHP_INI_RECOMMENDED" "$PHP_INI_DIR/php.ini"
		sed -i -e "s/^ *memory_limit.*/memory_limit = 4G/g" "$PHP_INI_DIR/php.ini"
	fi

	mkdir -p var/cache var/log data/user/avatars data/gallery/member upload/images public/bundles
	CURRENT_UID="$(id -u)"
	ACL_PATHS="var data upload public/bundles"
	if [ "$APP_ENV" != 'prod' ]; then
		ACL_PATHS="$ACL_PATHS build"
	fi
	setfacl -R -m u:www-data:rwX -m u:"${CURRENT_UID}":rwX $ACL_PATHS 2>/dev/null || true
	setfacl -dR -m u:www-data:rwX -m u:"${CURRENT_UID}":rwX $ACL_PATHS 2>/dev/null || true
	if [ "$APP_ENV" != 'prod' ] && [ -f /certs/localCA.crt ]; then
		ln -sf /certs/localCA.crt /usr/local/share/ca-certificates/localCA.crt
		update-ca-certificates
	fi

	if [ "$APP_ENV" != 'prod' ] && [ -f rox_docker.ini ]; then
		cp rox_docker.ini rox_local.ini
	fi

	if [ "$APP_ENV" != 'prod' ]; then
		git config --global --add safe.directory /srv/bewelcome 2>/dev/null || true
		if [ ! -f VERSION ]; then
			git rev-parse --short HEAD > VERSION
		fi
	fi

	if [ "$APP_ENV" != 'prod' ]; then
		composer install --prefer-dist --no-progress --no-interaction --no-scripts
		bun i --frozen-lockfile
	fi

	ensure_composer_dependencies

	if [ -f .env ]; then
	    database_host=$(grep '^DB_HOST=' .env | cut -f 2 -d '=')
	    database_port=$(grep '^DB_PORT=' .env | cut -f 2 -d '=')
	    database_name=$(grep '^DB_NAME=' .env | cut -f 2 -d '=')
	    database_user=$(grep '^DB_USER=' .env | cut -f 2 -d '=')
	    database_password=$(grep '^DB_PASS=' .env | cut -f 2 -d '=')
	fi

	# Fall back to environment variables if .env is absent or values are empty
	database_host="${database_host:-${DB_HOST:-db}}"
	database_port="${database_port:-${DB_PORT:-3306}}"
	database_name="${database_name:-${DB_NAME:-bewelcome}}"
	database_user="${database_user:-${DB_USER:-bewelcome}}"
	database_password="${database_password:-${DB_PASS:-bewelcome}}"

	echo "Waiting for db to be ready..."
	db_wait_seconds=0
	db_wait_max="${BEWELCOME_DB_READY_MAX_WAIT_SECONDS:-300}"
	until php -r "new PDO('mysql:host=${database_host};port=${database_port};dbname=${database_name}', '${database_user}', '${database_password}');" > /dev/null 2>&1; do
		if [ "$db_wait_seconds" -ge "$db_wait_max" ]; then
			echo "Database not ready after ${db_wait_max}s; aborting." >&2
			exit 1
		fi
		sleep 1
		db_wait_seconds=$((db_wait_seconds + 1))
	done

	echo "db ready!"

	# In prod: clear and recompile the Symfony DI container from the current
	# image's config BEFORE running migrations. Without this, a stale compiled
	# container (from a previous deploy's var/ volume) that references a renamed
	# or removed class will crash doctrine:migrations:migrate.
	if [ "$APP_ENV" = 'prod' ]; then
		echo "Warmup cache"
		bin/console cache:clear
	fi

	if [ "$APP_ENV" != 'prod' ]; then
		echo "Creating database..."
		bin/console test:database:create --drop --force --no-interaction
		echo "Database created."

		echo "Importing translations"
		if [ -f docker/db/word.sql ]; then
			echo "Yepp, really. Importing translations"
			mariadb "$database_name" -u "$database_user" -p"$database_password" -h "$database_host" --port="$database_port" < docker/db/word.sql
		fi
		if [ -f docker/db/geonamesadminunits.sql ]; then
			mariadb "$database_name" -u "$database_user" -p"$database_password" -h "$database_host" --port="$database_port" < docker/db/geonamesadminunits.sql
		fi
    fi

	bin/console translations:add:missing > /dev/null

	if [ "$APP_ENV" = 'prod' ] && ls -A Migrations/*.php > /dev/null 2>&1; then
		bin/console doctrine:migrations:migrate --no-interaction
	fi

	# WarmUp translations now database is up to date
	if [ "$APP_ENV" != 'prod' ]; then
		composer run-script post-install-cmd
		composer dump-autoload --classmap-authoritative
		echo "Warmup cache"
		bin/console cache:clear
	else
		bin/console assets:install public
	fi

	if [ "$APP_ENV" != 'prod' ]; then
		bun encore dev --mode=development
	fi

	# Manticore indexing must not block HTTP when search data or schema is not ready yet.
	bin/console manticore:indices:forum || echo "Warning: manticore:indices:forum failed (exit $?); container continues."
	bin/console manticore:indices:geonames || echo "Warning: manticore:indices:geonames failed (exit $?); container continues."
fi

exec "$@"
