# Docker deployment

## Production image (`bewelcome_php`)

The default `docker-compose.yml` builds the **`bewelcome_php`** stage. That image is self-contained:

- **PHP dependencies** — `composer install --no-dev` during `docker build` (`vendor/` in the image).
- **Frontend assets** — `bun encore production` during `docker build` (`public/build/entrypoints.json` and hashed assets in the image).
- **No bind mount** — the app runs from baked-in `/srv/bewelcome`; no host `vendor/` or `public/build/` required.

Rebuild and redeploy after PHP or frontend changes:

```bash
docker compose build app
docker compose up -d
```

Verify assets are present in a built image:

```bash
docker build --target bewelcome_php -t bewelcome/app .
docker run --rm --entrypoint sh bewelcome/app -c 'test -f public/build/entrypoints.json'
```

`public/build/` stays gitignored; production must not rely on committing compiled assets.

## Local development

Copy `docker-compose.override.yml.dist` to `docker-compose.override.yml` (see `make install`). That override:

- Builds **`bewelcome_php_dev`**
- Bind-mounts the repository into `/srv/bewelcome`

The entrypoint runs `bun encore dev` when `APP_ENV` is not `prod`, so asset changes on the host are compiled inside the container during development.
