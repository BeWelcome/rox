#!/bin/sh
# Follows the setup of the php container (docker/php/docker-entrypoint.sh) until the app is ready.
# The devcontainer runs it on attach; elsewhere: docker compose exec php sh docker/php/setup-progress.sh

status=/tmp/bewelcome-setup.status
shown=0

echo "BeWelcome setup: the first start takes a few minutes, later starts are faster."
while :; do
	if [ -s "$status" ]; then
		lines=$(wc -l < "$status")
		# The container restarted and setup began again.
		if [ "$lines" -lt "$shown" ]; then
			shown=0
		fi
		if [ "$lines" -gt "$shown" ]; then
			start=$(head -n 1 "$status" | cut -d ' ' -f 1)
			sed -n "$((shown + 1)),${lines}p" "$status" | while read -r time step; do
				if [ "$step" = ready ]; then
					echo "[$((time - start))s] Ready: http://localhost (Mailpit: http://localhost:1080)"
				else
					echo "[$((time - start))s] $step"
				fi
			done
			shown=$lines
		fi
		if [ "$(tail -n 1 "$status" | cut -d ' ' -f 2-)" = ready ]; then
			exit 0
		fi
	fi
	sleep 2
done
