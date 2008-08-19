# a bit crude, but good and simple enough for local installations
chmod -R a+w htdocs/bw/memberphotos/
chmod -R a+w data
for i in build modules tools pthacks extensions extensions/minimal extensions/newskin extensions/otherskin; do 
	touch $i/autoload.cache.ini $i/alias.cache.ini;
	chmod a+w $i/autoload.cache.ini $i/alias.cache.ini;
done
