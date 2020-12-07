.PHONY: all build phpcpd phploc phpmd php-cs-fixer php-code-sniffer phpmetrics phpunit version
export COMPOSER_MEMORY_LIMIT := -1

SRC_DIR=src tests
SRC_DIR_NO_TESTS=src

null  :=
SPACE := $(null) $(null)
COMMA := ,
SRC_DIR_COMMA := $(subst $(SPACE),$(COMMA),$(SRC_DIR))

all: phpci

phpci: phpcpd phploc phpmd php-code-sniffer phpunit infection version

install:
	git rev-parse --short HEAD > VERSION
	test -f docker-compose.override.yml || cp docker-compose.override.yml.dist docker-compose.override.yml
	curl https://downloads.bewelcome.org/for_developers/rox_test_db/languages.sql.bz2 -o ./docker/db/languages.sql.bz2
	curl https://downloads.bewelcome.org/for_developers/rox_test_db/words.sql.bz2 -o ./docker/db/words.sql.bz2
	bunzip2 --force ./docker/db/languages.sql.bz2 ./docker/db/words.sql.bz2
ifdef root
		sudo docker-compose up -d
else
		docker-compose up -d
endif

install-geonames:
	curl http://download.geonames.org/export/dump/allCountries.zip > docker/db/allCountries.zip
	curl http://download.geonames.org/export/dump/alternateNames.zip > docker/db/alternateNames.zip
	curl http://download.geonames.org/export/dump/countryInfo.txt > docker/db/countryInfo.txt
	unzip docker/db/allCountries.zip -d docker/db/
	unzip docker/db/alternateNames.zip -d docker/db/
	rm docker/db/*.zip
ifdef root
		sudo docker-compose exec php sh -c "mysql bewelcome -u bewelcome -pbewelcome -h db < import.sql"
else
		docker-compose exec php sh -c "mysql bewelcome -u bewelcome -pbewelcome -h db < import.sql"
endif

phpcsfix:
	"./vendor/bin/phpcbf" $(SRC_DIR)
	"./vendor/bin/php-cs-fixer" fix -v

deploy: composer yarn encore assets

composer:
	composer install --prefer-dist --no-progress --no-suggest --no-interaction --no-scripts

yarn:
	yarn install

encore:
	yarn encore production

assets:
	php bin/console assets:install --env=prod

build:
	yarn encore dev
	php bin/console assets:install

phpdox: phploc phpmd php-code-sniffer phpunit
	"./vendor/bin/phpdox"

mkdocs:
	mkdocs build

phpcpd:
	"./vendor/bin/phpcpd" $(SRC_DIR_NO_TESTS) --progress --no-interaction --exclude=Entity --exclude=Repository

phploc:
	"./vendor/bin/phploc" --log-xml=phploc.xml $(SRC_DIR)

phpmd:
	"./vendor/bin/phpmd" $(SRC_DIR_COMMA) text phpmd.xml

php-cs-fixer:
	"./vendor/bin/php-cs-fixer" fix -v --diff --dry-run --warning-severity=0

php-code-sniffer:
	"./vendor/bin/phpcs"  --colors --warning-severity=Error

phpunit:
	phpdbg -qrr bin/phpunit --coverage-xml=build/logs/phpunit/coverage-xml --coverage-clover=build/logs/phpunit/clover.xml --log-junit=build/logs/phpunit/junit.xml --colors=never

infection: phpunit
	"./vendor/bin/infection" --only-covered --coverage=build/logs/phpunit --min-covered-msi=85 --threads=30

phpmetrics:
	"./vendor/bin/phpmetrics" --exclude=src/App/Entity --report-violations=phpmetrics.xml $(SRC_DIR_COMMA)

version:
	git rev-parse --short HEAD > VERSION
