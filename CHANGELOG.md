* Separate Rox\ namespace into modules (under `module/` directory)
  * Routes are included as part of module config, but Symfony still requires a
  master routes files, which is stored in `config/`
* Added a high level level config dir at `config/`. Glob inclusion allows files
to be environment specific or gitignored for local dev.
* Added FrameworkBundle and TwigBundle
* Added Carbon for better DateTime handling. Works well with Twig, and Eloquent
has native support for it.
* Use Symfony kernel for all requests, but fallback to a legacy kernel which
wraps the older request lifecycle
* index.php updated to be more like Symfony and delegate more to the Application
and Kernel classes
* index.php sets the working directory to the project root (instead of htdocs
previously.)
* Caching home page stats and translation catalogue - much faster home loading
now
* Twig extension `Rox\Core\Extension\AssetExtension` provides an `asset`
function to swap unversioned asset URLs in templates to the versioned URLs.
* FontAwesome and Lato font are now pulled via npm and copied with Grunt. The
Signika font is not available through npm, so it is committed with the Core
module.
* Upon successful login, add $_SESSION['IdMember'] value to native Symfony
session implementation
* Added vlucas/phpdotenv for better environment config management. Currently
only has `APP_ENV` and `APP_DEBUG`. The existing rox ini file remains for now.
Copy `.env.dist` to `.env` and adjust as required.
* Faker added to dev dependencies and Twig global various. This can be used to
generate fake data when the anonymised Rox data is not sufficient for testing.
* Removed grunt-autoprefixer - the file it modifies is generated from sass
* Add various new Gruntfile tasks: eg. jshint for JS linting, uglify for JS
minification, cssmin
* Add grunt-asset-versioning which generates versioned css and js files plus a
manifest file to map for example 'styles.css' to 'styles.a0f1dd.css'
* Front end dependencies removed from Composer and added to NPM. Grunt tasks
then copy from `node_modules/` to `htdocs/`.
