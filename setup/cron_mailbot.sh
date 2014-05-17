#!/bin/bash
cd /var/www/$1
env SERVER_NAME=$1
php htdocs/bw/mailbot.php

