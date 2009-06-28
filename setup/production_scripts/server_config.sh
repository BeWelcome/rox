host_tags="common host_$HOSTNAME"
mysql_file=`echo /etc/rc3.d/S[0-9][0-9]mysql`
[ -x "$mysql_file" ] && tags="$tags mysql"
if [ -d "/var/www/www.bewelcome.org" ] || [ -d "/home/bwrox/www.bewelcome.org" ]
then 
    host_tags="$tags production www.bewelcome.org"
fi
## todo: check for bwrox and other stuff

export host_tags
