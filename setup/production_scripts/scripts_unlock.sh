#!/bin/bash
[ "$USER" == "root" ] || { echo "this script should only be run as root user"; exit 255; }

n=${PWD//\//\ }
m=${n##* }
if [ "$m" != "production_scripts" ]
then
    echo "You're running this script in a place it should be run!"
    exit 255
fi

find . -maxdepth 1 -type f -exec chown 'bwrox:bwrox' '{}' \;
find . -maxdepth 1 -type f -exec chmod 0755 '{}' \;
chown bwrox:bwrox .
chmod 0755 .
echo "Shell scripts folder unlocked"
