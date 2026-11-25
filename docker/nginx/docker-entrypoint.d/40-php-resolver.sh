#!/bin/sh
# default.conf asks Docker's embedded DNS (127.0.0.11) for php at request time (#720).
# Other engines serve DNS elsewhere (Podman: the network gateway, Kubernetes: CoreDNS), so use
# the container's own nameserver there. With Docker this script changes nothing.

conf=/etc/nginx/conf.d/default.conf
nameserver=$(awk '$1 == "nameserver" { print $2; exit }' /etc/resolv.conf)

if [ -z "$nameserver" ] || [ "$nameserver" = 127.0.0.11 ]; then
	exit 0
fi

# nginx wants an IPv6 address in brackets
case "$nameserver" in
	*:*) nameserver="[$nameserver]" ;;
esac

if sed -i "s/resolver 127\.0\.0\.11 /resolver $nameserver /" "$conf"; then
	echo "$0: nginx resolves php through $nameserver"
else
	echo "$0: could not set the resolver in $conf, it stays 127.0.0.11" >&2
fi
