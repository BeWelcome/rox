# Rox the software running BeWelcome.org 

[![Build Status](https://travis-ci.org/BeWelcome/rox.svg?branch=bootstrap4)](https://travis-ci.org/BeWelcome/rox)

**A community-driven hospitality exchange network**

![Image of BeWelcome Startpage](https://raw.githubusercontent.com/BeWelcome/bewelcome.github.io/master/images/startpage%20bewelcome.png)

Check [INSTALL](INSTALL.md) for installation instructions.

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

Fork a repository on Github. Work on what you like to update and send a pull request to merge it into the main repository.

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
* [Legacy developer space on Trac](http://trac.bewelcome.org/)
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
