#!/bin/bash
if [ -z "$1" ]
then
    echo "Called without any arguments. Must be called with host name of server, like test.bewelcome.org"
    exit 1
fi

file=`date +%Y-%m-%d`
if `/usr/bin/wget --header="Host: $1" http://localhost/members/dataretention -O /tmp/dataretention$file > /dev/null 2>&1`
then
    if /bin/grep -q "^success$" /tmp/dataretention$file && /bin/rm /tmp/dataretention$file
    then
        exit 0
    else
        error="Couldn't remove members for data retention on $1"
    fi
else
    error="Couldn't remove members for data retention on $1"
fi
echo $error | mail -s "Data retetention failed" bw-admin-discussion@bewelcome.org
