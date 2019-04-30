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
* PHP short opening tags enabled
* PHP magic quotes gpc disabled
* PHP extensions: mbstring, dom, fileinfo, intl, xsl
* MariaDB >=10.1
* SMTP server for email features
* [Composer](https://www.getcomposer.org) Latest version
* [Node.js](https://nodejs.org/) Latest version
* [Sphinxsearch](http://sphinxsearch.com/) (optional)
* wget (if you want to follow the instructions word to word)

### Download

1. Clone via Git:

    ```bash
    $ git clone https://github.com/BeWelcome/rox.git
    ```

2. For a first look around a read-only clone will do. If you want to support development please fork the repository and send pull requests.

### Create files and set permissions

1.  Create database and set privileges:

    ```bash
    $ sudo mysql -u root -p
    mysql> CREATE DATABASE bewelcome;
    mysql> GRANT ALL PRIVILEGES ON bewelcome.* TO 'bewelcome'@'localhost' IDENTIFIED BY 'bewelcome';
    mysql> FLUSH PRIVILEGES;
    mysql> exit
    ```

1.  Change to BW-Rox directory:

    ```
    $ cd /path/to/rox
    ```

2. Create data directory for image uploads and working files:

    ```bash
    $ mkdir data
    ```

### Initialize installation

8. Install the rox dependencies

    ```bash
    $ php composer.phar install
    $ npm install
    ```

1. Execute
 
    If your database parameters are different from above copy ```.env``` to ```.env.local``` and match with your MYSQL credentials.       

2.  Initialize the database.

    This generates a new database as given in the ```.env.*``` files and presets some data.
        
    ```bash
    $ php bin/console doctrine:schema:create
    $ php bin/console hautelook:fixtures:load
    ``` 
   
10. To get translated keywords update words and language tables to match the current translation on the site

    ```bash
    $ wget http://downloads.bewelcome.org/for_developers/rox_test_db/languages.sql.bz2
    $ wget http://downloads.bewelcome.org/for_developers/rox_test_db/words.sql.bz2
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
    $ php bin/console server:start
   ```

   (On Windows use php bin/console server:run)

   Access the site using http://localhost:8000/

4. Log in as user `member-1` and password `password`. See [Useful hints](#useful-hints) section below
   on password usage.

5. Click around the site a bit and check if all CSS and images are loaded.
   Refer to var/log/dev.log if errors appear or something looks broken. Also make use of the Symfony3 debug toolbar.

## Useful hints

* Geographical data:

    There is exactly one city in the dump: Berlin.

* Resetting all user passwords:

    ```bash
    $ mysql bewelcome -u bewelcome -pbewelcome
    ```

    ```sql
    mysql> UPDATE members SET password = PASSWORD('password');
    mysql> exit
    ```

* When doing bigger updates clear the cache from time to time with

    ```bash
    $ php bin/console cache:clear
    ```
    
## Create documentation

If you need documentation check out mkdoc.

## Further help

* [Mailing list](http://lists.bewelcome.org/mailman/listinfo/bw-dev-discussion)
