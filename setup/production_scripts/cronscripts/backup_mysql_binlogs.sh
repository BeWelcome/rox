#!/bin/bash
cd /home/bwrox/logs/mysql/
for file in mysql-bin*
do
  sudo fuser -s $file && continue
  gpg -e -r bw-admin-discussion@bewelcome.org < $file > /home/bwrox/backups/binlogs/$file.pgp &&
    rm $file || echo "binlog backup failed - could not encrypt $file"
  scp -qi /home/bwbackup/.ssh/id_rsa /home/bwrox/backups/binlogs/$file.pgp bwbackup@mule.bewelcome.org:/var/backup/deer/mysql-binlogs/
done
