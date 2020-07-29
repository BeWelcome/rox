# Rox the software running BeWelcome.org :earth_asia:

[![Build Status](https://travis-ci.org/BeWelcome/rox.svg?branch=bootstrap4)](https://travis-ci.org/BeWelcome/rox)
[![GitHub CI](https://github.com/BeWelcome/rox/workflows/CI/badge.svg)](https://github.com/BeWelcome/rox/actions?query=workflow%3ACI)

**A community-driven hospitality exchange network**

![Image of BeWelcome Startpage](https://raw.githubusercontent.com/BeWelcome/bewelcome.github.io/master/images/startpage%20bewelcome.png)

# Why this is incredible :heart_eyes:
* :sleeping_bed: **Member profiles** with focus on finding a place to stay
* :mag_right: **Search members** by map, location, username
* :two_men_holding_hands: **Comment system** to increase trust between each other
* :pencil: **Forum and groups** for discussions
* :rowboat::bicyclist: **Activities, galleries** to show who you are
* :wrench: **Volunteer tools** (safety, moderation, spam, rights member welcome tools and more)
* :rainbow: **On page translation** for 305 languages
* :raising_hand::muscle: BeWelcome is people and volunteers [Learn more](https://www.bewelcome.org/about)

# Join the team :girl::boy::woman::man:

You like the idea? Development is only one way to contribute! Find out how to [get active](https://www.bewelcome.org/about/getactive), including as designer, tester, translator, moderator, helping others and much more! :heart_eyes:

## Get your Rox development enviroment :computer:

1. :balloon: [Set up you local development enviroment](INSTALL.md) and fork the repository on Github.
2. :mag: Pick a [good starter issue](https://github.com/BeWelcome/rox/labels/good%20starter%20issue)
3. :sparkles: Create a [pull request](https://opensource.guide/how-to-contribute/#opening-a-pull-request) and `@mention` the people from the issue to review
4. :sun_with_face: Fix the remaining things during review
4. :tada: Wait for it being merged!

You probably want to get started by checking out the code in `src/`.

`build/` is deprecated and the code needs to be rewritten in `src/`.

## Documentation

Documentation is [in the doc tree](doc/book/) and can be compiled using
[mkdocs](http://www.mkdocs.org):

```bash
$ mkdocs build
```

The result can then be accessed via `doc/html/` in your cloned repository.

PHP API documentation can also be generated using
[phpDox.](https://github.com/theseer/phpdox) phpDox integrates with numerous
continuous integration tools, so we recommend using the following `make` task to
get the full output:

```bash
make phpdox
```

The result can then be accessed via `doc/phpdox/` in your cloned repository.

## Procedure

If you see an updated ```composer.json``` or ```composer.lock``` make sure to run

```bash
composer install
```

Also run

```bash
npm install
```

everytime you see a change in either ```package.json``` or ```package-lock.json```.

If any ```.scss``` file or a file in ```assets/``` changed a ```make build``` is necessary.

## Useful links
* [Writing great Git commit messages](http://chris.beams.io/posts/git-commit/)
* [Git crash course](http://git.or.cz/course/svn.html)


## Coding standards
* [PSR-1](http://www.php-fig.org/psr/psr-1/)
* [PSR-2](http://www.php-fig.org/psr/psr-2/)

To ensure coding standards are followed run ```make``` everytime before you commit. Fixing coding standard issues can be achieved with

```bash
make phpcsfix
```

twice in a row.
