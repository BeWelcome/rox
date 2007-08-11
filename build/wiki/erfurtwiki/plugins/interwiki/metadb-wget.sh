#!/bin/sh
# This script fetches the MetaWiki database file from our downloads/ dir
URL_DOWN="http://erfurtwiki.sourceforge.net/downloads/contrib-add-ons/metadb"
URL_MIRR="http://ewiki.berlios.de/metadb"
URL_ORIG="http://sunir.org/meatball/MetaWiki/metadb"

#-- switch to ewiki base, make EWIKI_VAR directory
cd `dirname $0`
cd ../..
[ ! -e var ]   &&   mkdir var   &&   chmod 763 var

#-- download
wget -N	$URL_MIRR -O var/metadb ||	\
wget -N	$URL_DOWN -O var/metadb	||	\
wget -N	$URL_ORIG -O var/metadb	||	\
echo "ERROR fetching latest 'metadb' file"

#-- compress
gzip -7 var/metadb

