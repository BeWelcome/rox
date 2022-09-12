# Installation of BW-Rox

1. Clone via Git:

    ```bash
    $ git clone https://github.com/BeWelcome/rox.git
    ```

    For a first look around a read-only clone will do. If you want to support development please fork the repository and send pull requests.

2. You can choose to install using A) Docker and Docker Compose or B) Installation step by step of BW-Rox (GNU/Linux) (see below)

## A) Install using Docker and Docker Compose

### Requirements

* [Docker](https://docs.docker.com/get-docker/)
* [Docker Compose](https://docs.docker.com/compose/install/)
* If running on Windows, we recommend installing WSL in order to execute the following linux commands. Note that the files of the repository should be put into the WSL file share and not below your user directory or someplace else easily accessible by Windows.

### Initialize installation

1. Install using Docker and Docker Compose:

    ```bash
    $ make install
    ```

   If you need to run Docker Compose as sudo, run the following:

    ```bash
    $ make install root=1
    ```
    
    <details>
    <summary><strong>Troubleshooting</strong></summary>
     Windows users may run into a build error concerning `composer clear-cache`. If you see this error, please try the following:
    - If you receive the error when running `make install`, try running `make install root=1`. Make sure to run the command with `root=1` after trying each of the following steps as well
    - Ensure that you are using WSL version 2 for your linux distro. See the following doc for [how to upgrade from WSL 1 to WSL 2](https://docs.microsoft.com/en-us/windows/wsl/install#upgrade-version-from-wsl-1-to-wsl-2)
    - Use Ubuntu 22.04 LTS as your linux distro - the error has been observed on earlier versions of Ubuntu, e.g. on 18.04 LTS
    </details>

Wait a few minutes for containers to build and start (it might take awhile). Project is running at
[http://localhost:8080](http://localhost:8080).

2. If you want to import geonames data, run the following (this operation takes awhile!):

    ```bash
    $ make install-geonames
    ```

    Once again, if you need to run Docker Compose as sudo, run the following:

    ```bash
    $ make install-geonames root=1
    ```

Please read [Useful hints](#useful-hints) section below.

## B) Installation step by step of BW-Rox (GNU/Linux)

These steps have been tested on Debian/Ubuntu based systems. Commands,
usernames and locations might differ on your distribution.

Windows users may use XAMPP and execute most of the commands in the git bash.
Commands for mysql need to be run in the XAMPP shell. Instead of wget either download
using a browser or use curl _url_ > _filename_.

### Requirements

* Apache with mod_rewrite enabled
* PHP version >= 7.4 < 8.0
* PHP GD lib enabled
* PHP magic quotes gpc disabled
* PHP extensions: mbstring, xml, fileinfo, intl, xsl, xmlrpc, (see composer.json)
* MariaDB >=10.1
* [symfony command line interface](https://symfony.com/download) (download/setup)
* SMTP server for email features
* [Composer](https://www.getcomposer.org) Version 2 (installed globally)
* [Node.js](https://nodejs.org/) Latest version (installed globally)
* [Yarn](https://classic.yarnpkg.com/en/docs/install/) Latest version
* [Sphinxsearch](http://sphinxsearch.com/) Version 3
* wget (if you want to follow the instructions word to word) otherwise curl and the -o parameter should be your friend

### Initialize installation

1. Install the rox dependencies using composer and yarn

    ```bash
    $ composer install
    $ yarn install --frozen-lock
    ```

2. Initialize the database.

    Copy .env into a .env.local file and edit the DB_HOST to point to localhost (assuming MariaDB runs locally).

    In mysql create a new global user `bewelcome` with password `bewelcome`.

    This generates a new database as given in the ```.env.local``` file and presets some data.

    ```bash
    $ php bin/console test:database:create --drop --force
    ```

### Test and log in

1. Run

   ```bash
    $ make build version
   ```

   to build the CSS and JS files. The version creates a file referenced in the footer.

2. Build sphinx indices and run search daemon

   Adapt setup/sphinx/sphinx-3.conf to match your needs (DB credentials and replace <path to indices> with your preferred folder path).

   Run the indexer to create indices:
   ```bash
   $ indexer --config indices-3.conf --all
   ```

   Serve the indices using the search daemon:
   ```bash
   $ searchd --config indices-3.conf
   ```

3. Start the server

   ```bash
    $ symfony serve
   ```

   Access the site using http://localhost:8000/ or with https://localhost:8000/ (if you installed certificate for symfony)

Please read [Useful hints](#useful-hints) section below.

3. (Optional) Load languages and translations

    ```bash
    $ wget https://downloads.bewelcome.org/for_developers/rox_test_db/languages.sql.bz2
    $ wget https://downloads.bewelcome.org/for_developers/rox_test_db/words.sql.bz2
    $ bunzip2 languages.sql.bz2 words.sql.bz2
    $ mysql bewelcome -u bewelcome -pbewelcome < languages.sql
    $ mysql bewelcome -u bewelcome -pbewelcome < words.sql
    ```

4. (Optional) Load geonames database (this operation takes awhile!)

    ```bash
    $ wget http://download.geonames.org/export/dump/allCountries.zip > docker/db/allCountries.zip
    $ wget http://download.geonames.org/export/dump/alternateNames.zip > docker/db/alternateNames.zip
    $ wget http://download.geonames.org/export/dump/countryInfo.txt > docker/db/countryInfo.txt
    $ unzip docker/db/allCountries.zip -d docker/db/
    $ unzip docker/db/alternateNames.zip -d docker/db/
    $ rm docker/db/*.zip
    $ mysql bewelcome -u bewelcome -pbewelcome < import.sql
    ```

    After this you should rebuilt the indices for sphinx:
   ```bash
   $ /usr/bin/indexer --config indices-3.conf --rotate --quiet --all
   ```

## Useful hints

* Log in as user `member-2` and password `password`.  There is also a user bwadmin which has some rights assigned (and uses the same password).

* Click around the site a bit and check if all CSS and images are loaded.
   Refer to var/log/dev.log if errors appear or something looks broken. Also make use of the Symfony debug toolbar.

* Geographical data (without optional geonames database):

    There are exactly two cities in the dump: Berlin and Jayapura.

* Resetting all user passwords:

    ```bash
    $ mysql bewelcome -u bewelcome -pbewelcome
    ```

    ```sql
    mysql> UPDATE members SET password = PASSWORD('password');
    mysql> exit;
    ```

* When doing bigger updates clear the cache from time to time with

    ```bash
    $ php bin/console cache:clear
    ```

* Production OS is Debian GNU/Linux Strech.

## Create documentation

If you need documentation check out [MKDocs](https://www.mkdocs.org/).
