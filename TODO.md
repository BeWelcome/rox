# TODO
* Add [PHP Debug Bar](https://github.com/maximebf/php-debugbar) with PDO query
log.
* Add a _CONTRIBUTING.md_ file.
* Get 'user rights' working again - integrate with Symfony permissions system?
* Restore the scriptfiles.html.twig / stylesheets.html.twig method of including
 additional assets.
* Set default language from Request::getPreferredLanguage() ?
* Keep setting IdMember value in session upon login so the old application still
 recognises logins.
* Logging out clears the session and therefore resets the language to back to
English
* Update phinx config to use new env config
* Add a way to enable 'remember me' from login on landing page. Maybe only show
checkbox when clicking into username field?
* [umpirsky/language-list](https://github.com/umpirsky/language-list) might be
useful for localised language lists.
* Create an environment bootstrap for phpunit.
* Separate unit tests and integration tests.
