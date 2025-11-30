#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- php-fpm "$@"
fi

if [ "$1" = 'php-fpm' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then
 	PHP_INI_RECOMMENDED="$PHP_INI_DIR/php.ini-production"
	if [ "$APP_ENV" != 'prod' ]; then
		PHP_INI_RECOMMENDED="$PHP_INI_DIR/php.ini-development"
	fi
	ln -sf "$PHP_INI_RECOMMENDED" "$PHP_INI_DIR/php.ini"
    sed -i -e "s/^ *memory_limit.*/memory_limit = 4G/g" "$PHP_INI_DIR/php.ini"

	mkdir -p var/cache var/log data/user/avatars data/gallery/member upload/images
	setfacl -R -m u:www-data:rwX -m u:"$(whoami)":rwX var build data upload
	setfacl -dR -m u:www-data:rwX -m u:"$(whoami)":rwX var build data upload

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
		yarn install --frozen-lock
		composer install --prefer-dist --no-progress --no-interaction --no-scripts
	fi

    database_host=$(grep '^DB_HOST=' .env | cut -f 2 -d '=')
    database_port=$(grep '^DB_PORT=' .env | cut -f 2 -d '=')
    database_name=$(grep '^DB_NAME=' .env | cut -f 2 -d '=')
    database_user=$(grep '^DB_USER=' .env | cut -f 2 -d '=')
    database_password=$(grep '^DB_PASS=' .env | cut -f 2 -d '=')

	echo "Waiting for db to be ready..."
	until mariadb $database_name -u $database_user -p$database_password -h $database_host --port=$database_port -e "select 1" > /dev/null 2>&1; do
		sleep 1
	done

	echo "db ready!"
	if [ "$APP_ENV" != 'prod' ]; then
		echo "Creating database..."
		bin/console test:database:create --drop --force --no-interaction
		echo "Database created."

		if [ -f docker/db/languages.sql ]; then
			mariadb $database_name -u $database_user -p$database_password -h $database_host --port=$database_port < docker/db/languages.sql
		fi
		if [ -f docker/db/words.sql ]; then
			mariadb $database_name -u $database_user -p$database_password -h $database_host --port=$database_port < docker/db/words.sql
		fi
		if [ -f docker/db/geonamesadminunits.sql ]; then
			mariadb $database_name -u $database_user -p$database_password -h $database_host --port=$database_port < docker/db/geonamesadminunits.sql
		fi

		bin/console translations:add:missing

	elif ls -A src/Migrations/*.php > /dev/null 2>&1; then
		bin/console doctrine:migrations:migrate --no-interaction
	fi
	# WarmUp translations now database is up to date
	composer run-script --no-dev post-install-cmd

	if [ "$APP_ENV" != 'prod' ]; then
		yarn encore dev --mode=development
	fi

	# create manticore indices
	bin/console manticore:indices:forum
	bin/console manticore:indices:geonames
fi

exec docker-php-entrypoint "$@"
