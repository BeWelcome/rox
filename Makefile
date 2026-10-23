.PHONY: all build check test security phpcpd phploc phpmd php-cs-fixer php-code-sniffer yaml-lint doctrine phpmetrics phpunit infection composer-audit yarn-audit behat version hooks
export COMPOSER_MEMORY_LIMIT := -1
# php-cs-fixer 3.4 (pinned through the Symfony 5.3 components) declares PHP <= 8.1.
# It runs fine on 8.3; the upgrade to a current release comes with the framework upgrade (#519).
export PHP_CS_FIXER_IGNORE_ENV := 1

SRC_DIR=src tests
SRC_DIR_NO_TESTS=src

null  :=
SPACE := $(null) $(null)
COMMA := ,
SRC_DIR_COMMA := $(subst $(SPACE),$(COMMA),$(SRC_DIR))
TIME_STAMP := $(shell git log -n 1 --format=%aI)
INFECTION_THREADS ?= $(shell nproc)

all: phpci

# The same checks as CI (.github/workflows/ci.yml calls these targets).
phpci: check test security phploc

# Fast checks, run by the pre-push hook.
check: php-cs-fixer php-code-sniffer phpmd phpcpd yaml-lint doctrine

test: phpunit infection

security: composer-audit yarn-audit

hooks:
	git config core.hooksPath .githooks

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
	"./vendor/bin/phpcpd" $(SRC_DIR_NO_TESTS) --exclude=src/Entity

phploc:
	"./vendor/bin/phploc" --log-xml=phploc.xml $(SRC_DIR)

phpmd:
	"./vendor/bin/phpmd" $(SRC_DIR_COMMA) text phpmd.xml

php-cs-fixer:
	"./vendor/bin/php-cs-fixer" fix -v --diff --dry-run

php-code-sniffer:
	"./vendor/bin/phpcs"  --colors --warning-severity=Error

yaml-lint:
	bin/console lint:yaml --parse-tags config fixtures

doctrine:
	bin/console doctrine:schema:validate --skip-sync

phpunit:
	XDEBUG_MODE=coverage bin/phpunit --coverage-text --coverage-xml=build/logs/phpunit/coverage-xml --coverage-clover=build/logs/phpunit/clover.xml --log-junit=build/logs/phpunit/junit.xml --colors=never

# Uses the coverage of the last `make phpunit`.
# Ratchet: covered-code MSI on main was 79% on 2026-10-09 (develop: 85%); 1 point below for run-to-run noise. Raise it, never lower it.
infection:
	"./vendor/bin/infection" --only-covered --coverage=build/logs/phpunit --min-covered-msi=78 --threads=$(INFECTION_THREADS)

# Abandoned packages are left for the framework upgrade (#519); this fails on security advisories only.
composer-audit:
	composer audit --locked --abandoned=ignore --no-interaction

# Runtime dependencies only, as on develop; build tooling (Encore 4, CKEditor 40 build utils) moves with the upgrade (#519).
yarn-audit:
	yarn audit --groups dependencies

behat: encore
	bin/console doctrine:database:create --env=test --if-not-exists
	bin/console doctrine:schema:create --env=test
	bin/console hautelook:fixtures:load --env=test --no-interaction
	vendor/bin/behat --colors --tags='~@wip'

phpmetrics:
	"./vendor/bin/phpmetrics" --exclude=src/App/Entity --report-violations=phpmetrics.xml $(SRC_DIR_COMMA)

version:
	git rev-parse --short HEAD > VERSION
	touch -d $(TIME_STAMP) VERSION
