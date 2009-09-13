#!/bin/bash
[ "$USER" == "root" ] || { echo "this script should only be run as root user"; exit 255; }

n=${PWD//\//\ }
m=${n##* }
if [ "$m" != "production_scripts" ]
then
    echo "You're running this script in a place it should be run!"
    exit 255
fi

for file in `find .`
do
    if [[ $file != *.svn/* ]]
    then
        [ `chown bwrox:bwrox $file` ] || { echo "Failed to change owner of $file"; exit 255; }
    fi
done
echo "Shell scripts folder unlocked"
