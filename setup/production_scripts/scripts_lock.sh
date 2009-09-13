#!/bin/bash
[ "$USER" == "root" ] || { echo "this script should only be run as root user"; exit 255; }

n=${PWD//\//\ }
m=${n##* }
if [ "$m" != "production_scripts" ]
then
    echo "You're running this script in a place it should be run!"
    exit 255
fi

tmpfile=tempsvn.$$

`find . -maxdepth 1 -type f -exec chown 'root:bwrox' '{}' \;`
[ $? == 0 ] || { echo "Failed to change owner of files"; exit 255; }
find . -maxdepth 1 -type f -exec chmod 0755 '{}' \;
[ $? == 0 ] || { echo "Failed to change permissions on files"; exit 255; }
chown root:bwrox .
[ $? == 0 ] || { echo "Failed to change owner of files"; exit 255; }
chmod 0755 .
[ $? == 0 ] || { echo "Failed to change permissions on files"; exit 255; }

svnoutput=`svn st . 2>&1`
if [ -z "$svnoutput" ]
then
    echo "Shell scripts folder locked down"
else
    echo "There are modifications in the scripts folder, check to see if everything is right"
fi
