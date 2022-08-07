# Rox the software running BeWelcome.org

**A community-driven hospitality exchange network**

![Image of BeWelcome Startpage](https://raw.githubusercontent.com/BeWelcome/bewelcome.github.io/master/images/startpage%20bewelcome.png)

# Why this is incredible
*  **Member profiles** with focus on finding a place to stay
*  **Search members** by map, location, username
*  **Comment system** to increase trust between each other
*  **Forum and groups** for discussions
*  **Activities, galleries** to show who you are
*  **Volunteer tools** (safety, moderation, spam, rights member welcome tools and more)
*  **On page translation** for 305 languages
*  BeWelcome is people and volunteers [Learn more](https://www.bewelcome.org/about)

# Join the team

You like the idea? Development is only one way to contribute! Find out how to [get active](https://www.bewelcome.org/about/getactive), including as designer, tester, translator, moderator, helping others and much more!

## Get your Rox development enviroment

1. [Set up you local development enviroment](INSTALL.md) and fork the repository on Github.
2.  Pick a [good starter issue](https://github.com/BeWelcome/rox/labels/good%20starter%20issue)
3.  Create a [pull request](https://opensource.guide/how-to-contribute/#opening-a-pull-request) and `@mention` the people from the issue to review
4.  Fix the remaining things during review
4.  Wait for it being merged!

You probably want to get started by checking out the code in `src/`. (`build/` is deprecated and the code needs to be rewritten in `src/`.)

To make changes in **Javascript** bear in mind that the Webpack needs to process each change before it reflects on the site.
It is a good idea to run `yarn encore dev --watch` which will keep updating files as you keep saving them.

## Documentation

Documentation is [in the doc tree](doc/book/) and can be compiled using
[mkdocs](http://mkdocs.org/)

```bash
$ mkdocs build
```

The result can then be accessed via `doc/html/` in your cloned repository.

## Procedure

If you see an updated ```composer.json``` or ```composer.lock``` make sure to run

```bash
composer install --prefer-dist --no-progress --no-interaction --no-scripts
```

Also run

```bash
yarn install --frozen-lock
```

everytime you see a change in either ```package.json``` or ```yarn.lock```.

If any file in ```assets/``` changed a ```make build``` is necessary.

## Useful links
* [Writing great Git commit messages](http://chris.beams.io/posts/git-commit/)
* [Git crash course](http://git.or.cz/course/svn.html)


## Coding standards
* [PSR-1](http://www.php-fig.org/psr/psr-1/)
* [PSR-12](http://www.php-fig.org/psr/psr-12/)

To ensure coding standards are followed run ```make``` everytime before you commit. Fixing coding standard issues can be achieved with

```bash
make phpcsfix
```

twice in a row.
