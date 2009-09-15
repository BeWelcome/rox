## Host configuration file.  This may be a bit overkill for a setup with only
## two hosts, but at least it scales very well.  It's built on experiences 
## with a similar setup that tobixen has deployed at his workplace.

## HOST_TAGS contains all tags for this host.
## all hosts should have the "common"-tag
## all hosts should have the host_$HOSTNAME tag
HOST_TAGS="$HOST_TAGS common host_$HOSTNAME"

## Add mysql to the HOST_TAGS if applicable
MYSQL_STARTUP_FILE=`echo /etc/rc3.d/S[0-9][0-9]mysql`
[ -x "$MYSQL_STARTUP_FILE" ] && HOST_TAGS="$HOST_TAGS mysql"

## TODO: do something similar for apache, php, etc

## Add host tag "production" if the host is serving www.bewelcome.org
if [ -d "/var/www/www.bewelcome.org" ] || [ -d "/home/bwrox/www.bewelcome.org" ]
then 
    HOST_TAGS="$HOST_TAGS production"
fi

## Add site_* to host tags if applicable + find the scripts dir
for site in www.bewelcome.org test.bewelcome.org dev.bewelcome.org alpha.bewelcome.org www.bevolunteer.org
do
  for rootdir in /var/www /home/bwrox
  do
    [ -z "$SCRIPTS_DIR" ] && \
       [ -d "$rootdir/$site/setup/production_scripts/" ] && \
         SCRIPTS_DIR="$rootdir/$site/setup/production_scripts/"
    if [ -d "$rootdir/$site" ]
    then
      HOST_TAGS="$HOST_TAGS site_${site}"
      break
    fi
  done
done

## todo: check for bwrox and other stuff

export HOST_TAGS SCRIPTS_DIR
