# Installation (GNU/Linux)

Follow these steps to install BW-Rox.

These steps have been tested on Debian/Ubuntu based systems. Commands,
usernames and locations might differ on your distribution.

Windows users may use XAMPP and execute most of the commands in the git bash.
Commands for mysql need to be run in the XAMPP shell. Instead of wget either download
using a browser or use curl _url_ > _filename_.

## Requirements

* Apache with mod_rewrite enabled
* PHP version >= 5.6
* PHP GD lib enabled
* PHP short opening tags enabled
* PHP magic quotes gpc disabled
* PHP extensions: mbstring, dom, fileinfo, intl, xsl
* MariaDB >=10.1 or MySQL server >= 5.6 (not in strict mode) 
* SMTP server for email features
* [Composer](https://www.getcomposer.org)
* [Node.js](https://nodejs.org/)
* [Grunt](http://gruntjs.com/)
* [Ruby](http://www.ruby-lang.org/en/downloads/)
* [Sass](http://www.ruby-lang.org/en/downloads/)
* [Sphinxsearch](http://sphinxsearch.com/)
* wget (if you want to follow the instructions word to word)

### Download

1. Clone via Git:

    ```bash
    $ git clone https://github.com/BeWelcome/rox.git
    ```

2. Done. For a first look a simple read-only clone will do. See
 http://trac.bewelcome.org/wiki/Download for more details on branches and
 pushing code.


### Create files and set permissions

1. Change to BW-Rox directory:

    ```
    $ cd /path/to/rox
    ```

2. Create data directory for image uploads and working files:

    ```bash
    $ mkdir data
    ```
    ```bash
    $ touch \
      build/alias.cache.ini \
      build/autoload.cache.ini \
      modules/autoload.cache.ini \
      pthacks/autoload.cache.ini \
      tools/autoload.cache.ini \
      htdocs/exception.log
    ```

4. Make things writable by webserver:

    ```bash
    $ sudo chgrp www-data \
      data \
      build/autoload.cache.ini \
      modules/autoload.cache.ini \
      tools/autoload.cache.ini \
      pthacks/autoload.cache.ini \
      build/alias.cache.ini \
      htdocs/exception.log
    ```

    ```bash
    $ sudo chmod g+rw \
      data \
      build/autoload.cache.ini \
      modules/autoload.cache.ini \
      tools/autoload.cache.ini \
      pthacks/autoload.cache.ini \
      build/alias.cache.ini \
      htdocs/exception.log
    ```

### Database installation

1. Create database and set privileges:

    ```bash
    $ mysql -u root -p
    ```

    ```sql
    mysql> CREATE DATABASE bewelcome;
    mysql> GRANT ALL PRIVILEGES ON bewelcome.* TO 'bewelcome'@'localhost' IDENTIFIED BY 'bewelcome';
    mysql> FLUSH PRIVILEGES;
    mysql> exit
    ```

2. Download development database dump:

    ```bash
    $ wget http://downloads.bewelcome.org/for_developers/rox_test_db/bewelcome.sql.bz2
    ```

3. Uncompress dump:

    ```bash
    $ bunzip2 bewelcome.sql.bz2
    ```

4. Import dump into database (first line needs root because of routines):

    ```bash
    $ mysql bewelcome -u root -p < bewelcome.sql
    ```

5. Get geonames database tables, uncompress files and import into DB (can be skipped):

    Change to your BW-Rox directory and download the geonames tables:

    ```bash
    $ wget http://download.geonames.org/export/dump/allCountries.zip
    $ wget http://download.geonames.org/export/dump/alternateNames.zip
    $ wget http://download.geonames.org/export/dump/countryInfo.txt
    ```

    Unzip the files:

    ```bash
    unzip allCountries.zip
    unzip alternateNames.zip
    ```

    Import into the database:

    ```bash
    $ mysql --local-infile bewelcome -u bewelcome -pbewelcome < import.sql
    ```

    This will take a while as files are relatively large.

    Cleanup afterwards:

    ```bash
    $ rm allCountries.txt alternateNames.txt iso-languagecodes.txt allCountries.zip alternateNames.zip countryInfo.txt
    ```

8. Install the rox dependencies

    ```bash
    $ php composer.phar install
    $ npm install
    ```

9. Migrate the DB to the latest version

    ```bash
    php vendor/bin/phinx migrate -c phinx.php
    ```

10. Update words and language tables to match the current translation on the site

    ```bash
    $ wget http://downloads.bewelcome.org/for_developers/rox_test_db/languages.sql.bz2
    $ wget http://downloads.bewelcome.org/for_developers/rox_test_db/words.sql.bz2
    $ bunzip2 languages.sql.bz2 words.sql.bz2
    $ mysql bewelcome -u bewelcome -pbewelcome < languages.sql
    $ mysql bewelcome -u bewelcome -pbewelcome < words.sql
    ```

11. Remove dumps and other files:

    ```bash
    $ rm bewelcome.sql.bz2 bewelcome.sql languages.sql.bz2 languages.sql words.sql.bz2 words.sql
    ```

12. Configure Sphinxsearch (can be skipped)


### Configure Apache

1. Create a new virtual host file:

    ```bash
    $ sudo touch /etc/apache2/sites-available/bewelcome.conf
    ```

    1. Create self-signed certificate

        ```bash
        $ openssl req -nodes -x509 -newkey rsa:2048 -keyout key.pem -out cert.crt -subj '//CN=bewelcome' -days 2000
        ```

    2. Edit virtual host file, add basic settings and rewrites:

        ```
        <VirtualHost *:80>
          ServerName bewelcome
          DocumentRoot /path/to/rox/htdocs
          ErrorLog /var/log/apache2/bewelcome-error.log
          CustomLog /var/log/apache2/bewelcome-access.log combined
          php_admin_value error_reporting "E_ALL"
          <Directory /path/to/rox/htdocs>
              RewriteEngine On
              RewriteBase /
              RewriteCond %{REQUEST_FILENAME} !-f
              RewriteCond %{REQUEST_FILENAME} !-d
              RewriteRule ^/*([^/]*)\.php /bw/$1.php [L,R,QSA]
              RewriteCond %{REQUEST_FILENAME} !-f
              RewriteCond %{REQUEST_FILENAME} !-d
              RewriteRule ^.* index.php [L,QSA,PT]
          </Directory>
        </VirtualHost>
        ```

    Note: Running BW-Rox in a sub directory (e.g. http:/localhost/bewelcome/)
    instead of the server root works, but is not recommended, because it
    complicates the setup and might cause unexpected behaviour.

2. Add ServerName to your /etc/hosts file:

    ```
    127.0.0.1 bewelcome
    ```

3. Enable virtual host:

    ```bash
    $ sudo a2ensite bewelcome.conf
    $ sudo service apache2 reload
    ```


### Configure BW-Rox

1. Change to BW-Rox directory:

    ```bash
    $ cd /path/to/rox
    ```

2. Create rox_local.ini file and .env file

    ```bash
    $ cp rox_local.example.ini rox_local.ini
    $ cp .env.dist .env
    ```

3.  Modify to your needs (set DB name etc).
       

### Test and log in

1. Run 
    ```bash
    $ make
   ```

2. Run 
    ```bash
    $ make build
   ```
3. Point your browser to your BW-Rox installation (i.e. http://bewelcome/)

3. Log in as user `member-101` and password `password`. See [Useful hints](#useful-hints) section below
     on password usage.

3. Click around the site a bit and check if all CSS and images are loaded.
    Refer to /path/to/rox/htdocs/exception.log if errors appear or something
    looks broken.

## Useful hints

* Geographical data:

    There are sample geographical data included in the developer database dump.
    If you need more geographical data, import the geonames dump:
    http://downloads.bewelcome.org/for_developers/rox_test_db/geonames.sql.bz2

* Resetting all user passwords:

    ```bash
    $ mysql bewelcome -u bewelcome -pbewelcome
    ```

    ```sql
    mysql> UPDATE members SET password = PASSWORD('password');
    mysql> exit
    ```

## Create documentation

If you need documentation check out mkdoc.

## Further help

* [Mailing list](http://lists.bewelcome.org/mailman/listinfo/bw-dev-discussion)
