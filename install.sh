curl http://download.geonames.org/export/dump/allCountries.zip > allCountries.zip
curl http://download.geonames.org/export/dump/alternateNames.zip > alternateNames.zip
curl http://download.geonames.org/export/dump/countryInfo.txt > countryInfo.txt
unzip allCountries.zip
unzip alternateNames.zip
mysql -u bewelcome -pbewelcome bewelcome < import.sql
rm -rf allCountries.*
rm -f iso-languagecodes.txt
rm -rf alternateNames.*
rm -rf countryInfo.txt


