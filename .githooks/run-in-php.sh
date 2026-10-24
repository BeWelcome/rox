#!/bin/sh
# Runs a command where PHP and vendor/ are: here (the devcontainer, or a host with PHP),
# else in the running php container of docker compose. Exits 0 with a hint when neither is there.

if command -v php > /dev/null 2>&1 && [ -x vendor/bin/php-cs-fixer ]; then
	exec "$@"
fi

if command -v docker > /dev/null 2>&1 && docker compose ps --status running --services 2> /dev/null | grep -qx php; then
	exec docker compose exec -T php "$@"
fi

echo "$(basename "$0"): no PHP here and no running php container, skipping the checks." >&2
echo "Start the stack (docker compose up -d) or work in the devcontainer, then run: make check" >&2
exit 0
