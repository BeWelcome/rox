# Project Structure

The project is organized following the Symfony guidelines for 4.x (which are still valid for 5+).

* assets

  SCSS, Javascript, etc. files needed to style and work with the site.

* src

    All new code goes here. Subdirectories should have a clear name and point to the stuff in there.

* templates

    Stores the Twig templates for all pages, email notifications etc.

* tests

    Unit and integration tests. Executed on pull request on Github.
* translations

    Holds placeholder for all supported languages. Real translations are stored in the database. A copy can be obtained from downloads.bewelcome.org.

* config

    Application-wide config.

* var/log

    Logfiles

* migrations

    Old MySQL schema migrations. Not really needed anymore. Doctrine migrations go to src/Migrations.

* docker

    Files needed to create a docker setup.

* features

    Behat test files. Addressing API behavior. Run with make behat.

* fixtures

    Allows to seed a test database in case the docker image isn't used.

Most other directories contain old code that needs to be removed or migrated to the new code base.
