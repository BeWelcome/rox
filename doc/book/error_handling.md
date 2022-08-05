# Error Handling

## Error pages

Error page templates can be found in `module/Core/templates/twig/Exception`.

Which template is used and how errors are displayed to end users depend on the
exception type.

### Client exceptions (HTTP 400, 403, 404, etc.)

If a `NotFoundHttpException` or `AccessDeniedHttpException` is thrown, a
BeWelcome themed page will be shown with a friendly message, using the templates
`error404.html.twig` and `error403.html.twig`, respectively.

These two templates both extend the base BeWelcome `base.html.twig` template, so
they are shown in the context of the full website theme.

### Server exceptions (HTTP 500)

If an unhandled application exception is thrown, we cannot assume it is possible
to display the full website theme, so we must show a much simpler page using the
template `error.html.twig`.

Preferably this page should not include any external assets as we cannot
guarantee the state of the external environment. Any required CSS/JS should be
inline or in the `<head>` tag, and images embedded using the
[data URI scheme.](https://en.wikipedia.org/wiki/Data_URI_scheme)

### Debug mode

In both cases, debug mode will show stack traces using `exception_full.html.twig`
and `exception.html.twig` with the full website theme.
