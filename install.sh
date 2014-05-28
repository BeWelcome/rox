#!/bin/bash

# This will download all files necessary to import the full geonames dump
if hash curl 2>/dev/null; then
    curl http://download.geonames.org/export/dump/allCountries.zip > allCountries.zip
    curl http://download.geonames.org/export/dump/alternateNames.zip > alternateNames.zip
    curl http://download.geonames.org/export/dump/countryInfo.txt > countryInfo.txt
else
    wget http://download.geonames.org/export/dump/allCountries.zip > allCountries.zip
    wget http://download.geonames.org/export/dump/alternateNames.zip > alternateNames.zip
    wget http://download.geonames.org/export/dump/countryInfo.txt > countryInfo.txt
fi

unzip allCountries.zip
unzip alternateNames.zip

# This assumes that you setup your DB as outlined in INSTALL
mysql -u bewelcome -pbewelcome bewelcome < import.sql

# Some cleanup
rm -rf allCountries.*
rm -f iso-languagecodes.txt
rm -rf alternateNames.*
rm -rf countryInfo.txt


