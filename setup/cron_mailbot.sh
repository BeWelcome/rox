#!/bin/bash
cd /var/www/$1
env SERVER_NAME=$1
php tools/mailbot/mailbotclass..php

