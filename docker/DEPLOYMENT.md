# Docker deployment

## Production images (`bewelcome_php` + `bewelcome_nginx`)

The app runs as two containers built from the same `Dockerfile`:

- **`bewelcome_php`**: PHP-FPM. `composer install --no-dev` and `bun encore production`
  run during `docker build`, so `vendor/` and `public/build/` are in the image and
  nothing is bind-mounted in production.
- **`bewelcome_nginx`**: Nginx (`docker/nginx/conf.d/default.conf`). It serves the
  static files from `public/` (copied from the PHP stage) and passes PHP requests
  to `php:9000`.

CI builds and pushes both images (see the "Production image & deployment" section
in the [README](../README.md)). To build them locally:

```bash
docker build --target bewelcome_php -t bewelcome/php .
docker build --target bewelcome_nginx -t bewelcome/nginx .
docker run --rm --entrypoint sh bewelcome/php -c 'test -f public/build/entrypoints.json'
```

`public/build/` stays gitignored; production must not rely on committing compiled assets.

On start in `prod`, the PHP entrypoint clears the cache, adds missing translations
and warms the cache.

It doesn't run the Doctrine migrations: on production data they can take far longer
than the container's healthcheck allows. Run them by hand after the deploy:

```bash
docker exec bewelcome-php-1 bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
```

Until they have finished, pages that use a changed table can fail.

## Local development

Copy `docker-compose.override.yml.dist` to `docker-compose.override.yml` (see `make install`).
That override:

- builds **`bewelcome_php_dev`** (adds Xdebug, git and the MySQL client),
- bind-mounts the repository into `/srv/bewelcome`,
- publishes the app on http://localhost (port 80) and Mailpit on http://localhost:1080.

On start, the entrypoint installs Composer and bun dependencies, recreates the
database, imports `docker/db/word.sql` (translations), and runs `bun encore dev`.

Xdebug is installed but off. Set `XDEBUG_MODE=debug` on the `php` service to enable it.
