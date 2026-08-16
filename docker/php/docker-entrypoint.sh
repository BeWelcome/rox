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

	if [ "$APP_ENV" != 'prod' ] && [ ! -f config/jwt/private.pem ]; then
		jwt_passphrase=$(grep '^JWT_PASSPHRASE=' .env | cut -f 2 -d '=')
		if ! echo "$jwt_passphrase" | openssl pkey -in config/jwt/private.pem -passin stdin -noout > /dev/null 2>&1; then
			echo "Generating public / private keys for JWT"
			mkdir -p config/jwt
			echo "$jwt_passphrase" | openssl genpkey -out config/jwt/private.pem -pass stdin -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
			echo "$jwt_passphrase" | openssl pkey -in config/jwt/private.pem -passin stdin -out config/jwt/public.pem -pubout
#			setfacl -R -m u:www-data:rX -m u:"$(whoami)":rwX config/jwt
#			setfacl -dR -m u:www-data:rX -m u:"$(whoami)":rwX config/jwt
		fi
	fi

	if [ "$APP_ENV" != 'prod' ]; then
		yarn install --frozen-lock
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

	echo "Waiting for db to be ready..."
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
		bin/console test:database:create --drop --force --no-interaction

		if [ -f docker/db/languages.sql ]; then
			mysql $database_name -u $database_user -p$database_password -h $database_host < docker/db/languages.sql
		fi
		if [ -f docker/db/words.sql ]; then
			mysql $database_name -u $database_user -p$database_password -h $database_host < docker/db/words.sql
		fi
		if [ -f docker/db/geonamesadminunits.sql ]; then
			mysql $database_name -u $database_user -p$database_password -h $database_host < docker/db/geonamesadminunits.sql
		fi
	elif [ -z "${SKIP_DOCTRINE_MIGRATIONS}" ] && ls -A migrations/*.php > /dev/null 2>&1; then
		bin/console doctrine:migrations:migrate --no-interaction
	fi

	if [ -z "${SKIP_DOCTRINE_MIGRATIONS}" ]; then
		# WarmUp translations now database is up to date
		composer run-script --no-dev post-install-cmd
	fi

	# cache:clear runs as root; fix ownership so www-data can write at runtime
	chown -R www-data:www-data var/cache var/log

	if [ "$APP_ENV" != 'prod' ]; then
		yarn encore dev --mode=development
	fi
fi

exec docker-php-entrypoint "$@"
