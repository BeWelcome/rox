#!/bin/bash

# This will download all files necessary to import the full geonames dump
if hash curl 2>/dev/null; then
    curl http://download.geonames.org/export/dump/allCountries.zip > docker/db/allCountries.zip
    curl http://download.geonames.org/export/dump/alternateNames.zip > docker/db/alternateNames.zip
    curl http://download.geonames.org/export/dump/countryInfo.txt > docker/db/countryInfo.txt
else
    wget http://download.geonames.org/export/dump/allCountries.zip > docker/db/allCountries.zip
    wget http://download.geonames.org/export/dump/alternateNames.zip > docker/db/alternateNames.zip
    wget http://download.geonames.org/export/dump/countryInfo.txt > docker/db/countryInfo.txt
fi

unzip docker/db/allCountries.zip -d docker/db/
unzip docker/db/alternateNames.zip -d docker/db/

# This assumes that you setup your DB as outlined in INSTALL
mysql -u root bewelcome < import.sql

# Some cleanup
rm -rf docker/db/*.zip docker/db/*.txt
