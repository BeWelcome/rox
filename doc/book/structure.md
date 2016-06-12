# Project Structure

* cache

    All Rox filesystem caches. The cache segregated by environment, so
    you will find a sub-directory called `development`.

    Symfony has native caching, as defined in `Symfony\Component\HttpKernel\Kernel::getCacheDir()`

    Rox application caching through the Rox cache service (DoctrineCache
    implementation) is stored in `cache/%environment%/doctrine/`. See
    `config/cache.yml` for config.

* config

    Application-wide config.

    If you would like to define custom config here for development
    without committing it to the project repository, you can create a
    file ending in `local.yml`, such as `twig.local.yml`.

* htdocs

    Public web directory.

* logs

    As defined in `Symfony\Component\HttpKernel\Kernel::getLogDir`

* migrations

    MySQL schema migrations.

* module

    Rox system modules. All new code using Symfony3 goes here.

    Each module has a common structure:

    * assets

        CSS, JS, images specific to the module

    * config

        YAML config

    * src

        PHP source files (PSR-4)

    * templates

        Twig templates

    * tests

        PHPUnit tests
