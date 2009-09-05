#!/bin/bash
date=`date +%Y-%m-%d`
file1=BW_MAIN-$date.sql
file2=BW_ARCH-$date.sql
file3=BW_CRYPTED-$date.sql
file4=mysql-$date.sql
output=DB_BACKUP-$date.tar.bz2.pgp

mysqldump --routines BW_MAIN -r $file1
chmod 0600 $file1
mysqldump --routines BW_ARCH -r $file2
chmod 0600 $file2
mysqldump --routines BW_CRYPTED -r $file3
chmod 0600 $file3
mysqldump mysql -r $file4
chmod 0600 $file4
tar -cf - $file1 $file2 $file3 $file4 | bzip2 -c | gpg -e -r 'bw-admin-discussion@bewelcome.org' > ../bwrox/backups/$output
rm $file1 $file2 $file3 $file4
scp ../bwrox/backups/$output mule.bewelcome.org:/var/backup/deer

