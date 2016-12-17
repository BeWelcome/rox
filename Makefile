.PHONY: all build phpcpd phploc phpmd php-cs-fixer php-code-sniffer phpmetrics phpunit version

SRC_DIR=module/ src/ tests/TranslationLoader

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
	./vendor/bin/phpcpd $(SRC_DIR) --progress --no-interaction

phploc:
	./vendor/bin/phploc --log-xml=phploc.xml $(SRC_DIR)

phpmd:
	./vendor/bin/phpmd $(SRC_DIR_COMMA) text phpmd.xml

php-cs-fixer:
	./vendor/bin/php-cs-fixer fix -v --diff --dry-run

php-code-sniffer:
	./vendor/bin/phpcs

phpunit:
	./vendor/bin/phpunit

phpmetrics:
	./vendor/bin/phpmetrics --config=phpmetrics.yml

version:
	git rev-parse --short HEAD > VERSION

checkjs:
	./node_modules/.bin/grunt checkjs