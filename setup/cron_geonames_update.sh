#!/bin/bash
if [ -z "$1" ]
then
    echo "Called without any arguments. Must be called with host name of server, like test.bewelcome.org"
    exit 1
fi

file=`date +%Y-%m-%d`
if `/usr/bin/wget --header="Host: $1" http://localhost/geo/refreshgeo -O /tmp/geoupdate$file > /dev/null 2>&1`
then
    if /bin/grep -q "^success$" /tmp/geoupdate$file && /bin/rm /tmp/geoupdate$file
    then
        exit 0
    else
        error="Geo update was downloaded but failed on $1"
    fi
else
    error="Geo update failed on $1"
fi
echo $error | mail -s "Geo update failed" bw-admin-discussion@bewelcome.org
