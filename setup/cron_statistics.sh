#!/bin/bash
cd /var/www/www.bewelcome.org
env SERVER_NAME=www.bewelcome.org php /var/www/www.bewelcome.org/htdocs/bw/updatestats.php
