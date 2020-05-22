# Installation (GNU/Linux)

Follow these steps to install BW-Rox.

These steps have been tested on Debian/Ubuntu based systems. Commands,
usernames and locations might differ on your distribution.

Windows users may use XAMPP and execute most of the commands in the git bash.
Commands for mysql need to be run in the XAMPP shell. Instead of wget either download
using a browser or use curl _url_ > _filename_.

Production OS is Debian GNU/Linux Strech.

## Requirements

* Apache with mod_rewrite enabled
* PHP version >= 7.1 (and < 7.3)
* PHP GD lib enabled
* PHP magic quotes gpc disabled
* PHP extensions: mbstring, dom, fileinfo, intl, xsl
* MariaDB >=10.1
* SMTP server for email features
* [Composer](https://www.getcomposer.org) Latest version (installed globally)
* [Node.js](https://nodejs.org/) Latest version (installed globally)
* [Sphinxsearch](http://sphinxsearch.com/) (can be omitted but member search will be slow and forum search won't work)
* wget (if you want to follow the instructions word to word) otherwise curl and the -o parameter should be your friend

There is rudimentary Docker support. Feel free to update the Dockerfile to help fellow developers.

### Download

1. Download/setup [symfony command line interface](https://symfony.com/download)

1. Clone via Git:

    ```bash
    $ git clone https://github.com/BeWelcome/rox.git
    ```

    For a first look around a read-only clone will do. If you want to support development please fork the repository and send pull requests.

### Initialize installation

8. Install the rox dependencies using composer and npm

    ```bash
    $ composer install
    $ npm install
    ```

2.  Initialize the database.

	Create a new global user `bewelcome` with password `bewelcome`.

    This generates a new database as given in the ```.env``` file and presets some data.

    ```bash
    $ php bin/console test:database:create --drop --force
    ```

10. (Optional) Load

    ```bash
    $ wget https://downloads.bewelcome.org/for_developers/rox_test_db/languages.sql.bz2
    $ wget https://downloads.bewelcome.org/for_developers/rox_test_db/words.sql.bz2
    $ bunzip2 languages.sql.bz2 words.sql.bz2
    $ mysql bewelcome -u bewelcome -pbewelcome < languages.sql
    $ mysql bewelcome -u bewelcome -pbewelcome < words.sql
    ```

### Test and log in

2. Run

   ```bash
    $ make build version
   ```

   to build the CSS and JS files. The version creates a file referenced in the footer.

3. Start the server

   ```bash
    $ symfony serve
   ```

   Access the site using http://localhost:8000/ or with https://localhost:8000/ (if you installed certificate for symfony)

4. Log in as user `member-2` and password `password`. See [Useful hints](#useful-hints) section below
   on password usage. There is also a user bwadmin which has some rights assigned (and uses the same password).

5. Click around the site a bit and check if all CSS and images are loaded.
   Refer to var/log/dev.log if errors appear or something looks broken. Also make use of the Symfony4 debug toolbar.

## Useful hints

* Geographical data:

    There is exactly one city in the dump: Berlin.

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

## Create documentation

If you need documentation check out [MKDocs](https://www.mkdocs.org/).

## Further help

* [Mailing list](http://lists.bewelcome.org/mailman/listinfo/bw-dev-discussion)
