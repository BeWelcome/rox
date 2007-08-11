
# a stub for a real nice Makefile -- some day

# just start collecting some (more or less) sensible commands in here

debian:
	sudo apt-get install php5-gd apache2 mysql-server
	#cp /etc/apache2/mods-available/rewrite.load /etc/apache2/mods-enabled/
	#cp apache2/sites-enabled/001-bewelcome /etc/apache2/sites-enabled/
	chmod a+w ../data/
	/etc/init.d/apache2 restart

# svn co https://www.bewelcome.org/svn/develstuff/trunk/testdb/
# cd testdb
# gzip -d hcvoltest.sql.gz

#  mysql -u username -p
# mysql> CREATE DATABASE mtb
# mysql> Ctrl-D
#  mysql -u username -p mtb < hcvoltest.sql