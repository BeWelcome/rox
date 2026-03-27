#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- frankenphp run "$@"
fi

if [ "$1" = 'frankenphp' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ] || [ "$1" = 'ls' ]; then
 	PHP_INI_RECOMMENDED="$PHP_INI_DIR/php.ini-production"
	if [ "$APP_ENV" != 'prod' ]; then
		PHP_INI_RECOMMENDED="$PHP_INI_DIR/php.ini-development"
	fi
	ln -sf "$PHP_INI_RECOMMENDED" "$PHP_INI_DIR/php.ini"
    sed -i -e "s/^ *memory_limit.*/memory_limit = 4G/g" "$PHP_INI_DIR/php.ini"

	mkdir -p var/cache var/log data/user/avatars data/gallery/member upload/images
	setfacl -R -m u:www-data:rwX -m u:"$(whoami)":rwX var build data upload || true
	setfacl -dR -m u:www-data:rwX -m u:"$(whoami)":rwX var build data upload || true

	if [ "$APP_ENV" != 'prod' ] && [ -f /certs/localCA.crt ]; then
		ln -sf /certs/localCA.crt /usr/local/share/ca-certificates/localCA.crt
		update-ca-certificates
	fi

	if [ "$APP_ENV" != 'prod' ] && [ -f rox_docker.ini ]; then
		cp rox_docker.ini rox_local.ini
	fi

  git config --global --add safe.directory /srv/bewelcome
	if [ "$APP_ENV" != 'prod' ] && [ ! -f VERSION ]; then
		git rev-parse --short HEAD > VERSION
	fi

	if [ "$APP_ENV" != 'prod' ]; then
		composer install --prefer-dist --no-progress --no-interaction --no-scripts
		yarn install --frozen-lock
	fi

	if [ -f .env ]; then
	    database_host=$(grep '^DB_HOST=' .env | cut -f 2 -d '=')
	    database_port=$(grep '^DB_PORT=' .env | cut -f 2 -d '=')
	    database_name=$(grep '^DB_NAME=' .env | cut -f 2 -d '=')
	    database_user=$(grep '^DB_USER=' .env | cut -f 2 -d '=')
	    database_password=$(grep '^DB_PASS=' .env | cut -f 2 -d '=')
	fi

	# Fall back to environment variables if .env is absent or values are empty
	database_host="${database_host:-${DB_HOST}}"
	database_port="${database_port:-${DB_PORT}}"
	database_name="${database_name:-${DB_NAME}}"
	database_user="${database_user:-${DB_USER}}"
	database_password="${database_password:-${DB_PASS}}"


	echo "Waiting for db to be ready..."
	until mariadb $database_name -u $database_user -p$database_password -h $database_host --port=$database_port -e "select 1" > /dev/null 2>&1; do
		sleep 1
	done

	echo "db ready!"
	if [ "$APP_ENV" != 'prod' ]; then
		echo "Creating database..."
		bin/console test:database:create --drop --force --no-interaction
		echo "Database created."

        echo "Importing translations"
		if [ -f docker/db/word.sql ]; then
            echo "Yepp, really. Importing translations"
			mariadb $database_name -u $database_user -p$database_password -h $database_host --port=$database_port < docker/db/word.sql
		fi
		if [ -f docker/db/geonamesadminunits.sql ]; then
			mariadb $database_name -u $database_user -p$database_password -h $database_host --port=$database_port < docker/db/geonamesadminunits.sql
		fi

		bin/console translations:add:missing > /dev/nul

	elif ls -A src/Migrations/*.php > /dev/null 2>&1; then
		bin/console doctrine:migrations:migrate --no-interaction
	fi

    # WarmUp translations now database is up to date
    if [ "$APP_ENV" != 'prod' ]; then
        composer run-script post-install-cmd
        composer dump-autoload --classmap-authoritative
    else
        composer run-script --no-dev post-install-cmd
        composer dump-autoload --classmap-authoritative --no-dev
    fi

    echo "Warmup cache"
    bin/console cache:clear

	if [ "$APP_ENV" != 'prod' ]; then
		yarn encore dev --mode=development
	fi

	# create manticore indices
	bin/console manticore:indices:forum
	bin/console manticore:indices:geonames
fi

exec "$@"
