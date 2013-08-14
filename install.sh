wget http://download.geonames.org/export/dump/allCountries.txt
wget http://download.geonames.org/export/dump/alternateNames.txt
mysql -u bewelcome -pbewelcome < import.sql

