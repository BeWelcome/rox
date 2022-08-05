# Twig

Twig templates should be saved under the `templates` directory of each
module. They can be split into sub-directories for better organisation.

For example, the member profile template is stored in `module/Member/templates/profile/view.html.twig`
and can be rendered from Twig name `@member/profile/view.html.twig`

`@member` tells Twig the module namespace, as defined in `module/Member/config/twig.yml`
and `profile/view.html.twig` is the path within the `templates`
directory.

If a template is to be reused by multiple modules, save it in the Core
module, and use the `@base` module namespace with Twig.

## Warning

Due to the way Eloquent uses magic methods, Twig is unable to read
object relationships. Twig will end up using the function of the
relationship name (which returns a call to 'hasOne', for example)
instead of fetching the relationship via the `__get` function in the
underlying model. To get around this issue, each model must override the
`__isset` function to return true if a requested property is a known
relationship. See this [Stack Overflow answer](http://stackoverflow.com/a/35908957)
for more information.
