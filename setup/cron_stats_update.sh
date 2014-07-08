#!/bin/bash
if [ -z "$1" ]
then
    echo "Called without any arguments. Must be called with host name of server, like test.bewelcome.org"
    exit 1
fi

file=`date +%Y-%m-%d`
if `/usr/bin/wget --header="Host: $1" http://localhost/about/updatestats -O /tmp/statsupdate$file > /dev/null 2>&1`
then
    if /bin/grep -q "^success$" /tmp/statsupdate$file && /bin/rm /tmp/statsupdate$file
    then
        exit 0
    else
        error="Stats update failed on $1"
    fi
else
    error="Stats update failed on $1"
fi
echo $error | mail -s "Stats update failed" bw-admin-discussion@bewelcome.org
