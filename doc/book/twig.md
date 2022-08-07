# Twig

Twig templates should be saved under the `templates/` directory. They roughly follow the structure in `src\Controller`.

For example, the base template is stored in `/templates/base.html.twig`
and can be rendered from Twig using `base.html.twig`.

No namespaces are defined for Twig and aren't really needed. In controllers use templates including
subdirectories like `community/community.html.twig`.
