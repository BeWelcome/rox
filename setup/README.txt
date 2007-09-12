
In setup/ we can store everything that will make it easier for people
to set up a running BW Rox site. Feel free to add and improve this
stuff.

If you're on Debian or Ubuntu you can try something like

   sudo ln -s bewelcome/ /usr/local/src
   sudo cp bewelcome/setup/apache2/sites-enabled/001-bewelcome  /etc/apache2/sites-enabled/

or try

   sudo make debian

BW Rox will be running on http://localhost:1234