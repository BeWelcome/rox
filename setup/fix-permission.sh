# a bit crude, but good and simple enough for local installations
chmod -R a+w htdocs/bw/memberphotos/
chmod -R a+w data
for i in build modules tools pthacks; do touch pthacks/autoload.cache.ini; chmod a+w pthacks/autoload.cache.ini; done
