#!/bin/bash
if [ -z "$1" ]
then
    echo "Called without any arguments. Must be called with host name of server, like test.bewelcome.org"
    exit 1
fi

file=`date +%Y-%m-%d`
/usr/bin/wget --header="Host: $1" http://localhost/members/dataretention -O /tmp/dataretention$file > /dev/null 2>&1