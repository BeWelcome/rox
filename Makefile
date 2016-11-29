.PHONY: all build phpcpd phploc phpmd php-cs-fixer php-code-sniffer phpmetrics phpunit version

SRC_DIR=module/

null  :=
SPACE := $(null) $(null)
COMMA := ,
SRC_DIR_COMMA := $(subst $(SPACE),$(COMMA),$(SRC_DIR))

all: phpci

phpci: phpcpd phploc phpmd php-cs-fixer php-code-sniffer phpmetrics phpunit

phpcsfix:
	./vendor/bin/phpcbf module/
	./vendor/bin/php-cs-fixer fix -v

build:
	./node_modules/.bin/grunt

phpdox: phploc phpmd php-code-sniffer phpunit
	./vendor/bin/phpdox

mkdocs:
	mkdocs build

phpcpd:
	php -n -d memory_limit=256M ./vendor/bin/phpcpd $(SRC_DIR) --progress --no-interaction

phploc:
	php -n ./vendor/bin/phploc --log-xml=phploc.xml $(SRC_DIR)

phpmd:
	php -n ./vendor/bin/phpmd $(SRC_DIR_COMMA) text phpmd.xml

php-cs-fixer:
	php -n ./vendor/bin/php-cs-fixer fix -v --diff --dry-run

php-code-sniffer:
	php -n ./vendor/bin/phpcs

phpunit:
	./vendor/bin/phpunit --testsuite="Project Test Suite"

phpmetrics:
	php -n -d memory_limit=512M ./vendor/bin/phpmetrics --config=phpmetrics.yml

version:
	git rev-parse --short HEAD > VERSION

checkjs:
	./node_modules/.bin/grunt checkjs
