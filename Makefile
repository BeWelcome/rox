.PHONY: all build phpcpd phploc phpmd php-cs-fixer php-code-sniffer phpmetrics phpunit version

SRC_DIR=module/

null  :=
SPACE := $(null) $(null)
COMMA := ,
SRC_DIR_COMMA := $(subst $(SPACE),$(COMMA),$(SRC_DIR))

all: phpci

phpci: phpcpd phploc phpmd php-cs-fixer php-code-sniffer phpmetrics phpunit

build:
	./node_modules/.bin/grunt

phpcpd:
	php -d memory_limit=256M ./vendor/bin/phpcpd $(SRC_DIR) --progress --no-interaction

phploc:
	./vendor/bin/phploc $(SRC_DIR)

phpmd:
	./vendor/bin/phpmd $(SRC_DIR_COMMA) html phpmd.xml --reportfile phpmd.html

php-cs-fixer:
	./vendor/bin/php-cs-fixer fix -v --diff --dry-run

php-code-sniffer:
	./vendor/bin/phpcs

phpunit:
	./vendor/bin/phpunit

phpmetrics:
	php -d memory_limit=512M ./vendor/bin/phpmetrics --config=phpmetrics.yml

version:
	git rev-parse --short HEAD > VERSION

checkjs:
	./node_modules/.bin/grunt checkjs
