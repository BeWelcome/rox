# Rox the software running BeWelcome.org :earth_asia:

[![GitHub CI](https://github.com/BeWelcome/rox/workflows/CI/badge.svg)](https://github.com/BeWelcome/rox/actions?query=workflow%3ACI)

**A community-driven hospitality exchange network**

![Image of BeWelcome Startpage](https://raw.githubusercontent.com/BeWelcome/bewelcome.github.io/master/images/startpage%20bewelcome.png)

# Why this is incredible :heart_eyes:
* :sleeping_bed: **Member profiles** with focus on finding a place to stay
* :mag_right: **Search members** by map, location, username
* :two_men_holding_hands: **Comment system** to increase trust between each other
* :pencil: **Forum and groups** for discussions
* :rowboat::bicyclist: **Activities, galleries** to show who you are
* :wrench: **Volunteer tools** (safety, moderation, spam, rights member welcome tools and more)
* :rainbow: **On page translation** for 305 languages
* :raising_hand::muscle: BeWelcome is people and volunteers [Learn more](https://www.bewelcome.org/about)

# Join the team :girl::boy::woman::man:

You like the idea? Development is only one way to contribute! Find out how to [get active](https://www.bewelcome.org/about/getactive), including as designer, tester, translator, moderator, helping others and much more! :heart_eyes:

## Get your Rox development enviroment :computer:

1. :balloon: [Set up you local development enviroment](INSTALL.md) and fork the repository on Github. The quickest way is the devcontainer: open the repository in VS Code (or a GitHub Codespace) and choose "Reopen in Container". A terminal shows the setup progress; the first start takes a few minutes.
2. :mag: Pick a [good starter issue](https://github.com/BeWelcome/rox/labels/good%20starter%20issue)
3. :sparkles: Create a [pull request](https://opensource.guide/how-to-contribute/#opening-a-pull-request) and `@mention` the people from the issue to review
4. :sun_with_face: Fix the remaining things during review
4. :tada: Wait for it being merged!

You probably want to get started by checking out the code in `src/`.

`build/` is deprecated and the code needs to be rewritten in `src/`.

To make changes in **Javascript** bear in mind that the Webpack needs to process each change before it reflects on the site.
It is a good idea to run `bun encore dev --watch` which will keep updating files as you keep saving them.

## Documentation

Documentation is [in the doc tree](doc/book/).

## Procedure

If you see an updated ```composer.json``` or ```composer.lock``` make sure to run

```bash
composer install --prefer-dist --no-progress --no-interaction --no-scripts
```

Also run

```bash
bun i --frozen-lockfile
```

everytime you see a change in either ```package.json``` or ```bun.lock```.

If any ```.scss``` file or a file in ```assets/``` changed a ```make build``` is necessary.

## Production image & deployment :rocket:

The production images are **built in CI, not on the deploy server**. The deploy
host only pulls the images and restarts the stack — there is no on-server build and
no `composer install` on the server.

### How the images are built

The app runs as two containers, built from two targets of the [`Dockerfile`](Dockerfile):

* `bewelcome_php` — PHP-FPM with `vendor/` and the compiled front-end assets
  (`public/build/`) baked in.
* `bewelcome_nginx` — Nginx serving `public/` and passing PHP requests to `php:9000`.

Both are pushed as multi-arch (`linux/amd64` + `linux/arm64`) manifests to
`ghcr.io/bewelcome/rox`; the Nginx image carries a `-web` tag suffix. Each
architecture is built on a native runner (no QEMU) and pushed by digest, then a
merge job stitches the digests into one tagged manifest list.

| Workflow | Runs on | Tags |
| --- | --- | --- |
| [`build-image-stage`](.github/workflows/build-image-stage.yml) | pushes to `main` that touch image inputs | `stage` / `stage-web`, `sha-<shortsha>` / `sha-<shortsha>-web` |
| [`build-image-prod`](.github/workflows/build-image-prod.yml) | `vX.Y.Z` tag pushes | `X.Y.Z`, `X.Y`, `latest` (each also with `-web`) |
| [`pr-image-build`](.github/workflows/pr-image-build.yml) | pull requests to `main` | builds both images without pushing |

The PHP image is scanned with [Trivy](https://trivy.dev/) (CRITICAL/HIGH/MEDIUM;
unfixed CVEs only, exceptions in [`.trivyignore`](.trivyignore)). Results appear in
the **Scan image** job log.

After a successful stage build and scan, `build-image-stage` sends a
`repository_dispatch` (`event_type: rox-stage-image-pushed`) to
`BeWelcome/sysadmins-infra`, which redeploys stage with the new `sha-<shortsha>`
images.

### Cut a production release

```bash
git tag vX.Y.Z
git push --tags
```

This publishes the SemVer image tags and creates a GitHub release. Promote it to
production with the "Deploy prod" workflow in `sysadmins-infra`.

### Roll back

Redeploy a previous immutable tag from `sysadmins-infra` by pointing the deploy at
an earlier `ghcr.io/bewelcome/rox:sha-<shortsha>` (and its `-web` counterpart).

### Required secrets

The cross-repo dispatch authenticates as the `bewelcome-platform-deployer` GitHub
App (installed on `sysadmins-infra`). The workflow mints a short-lived installation
token at run time from two Actions secrets in this repo:

* `DEPLOY_APP_ID` — the App's ID
* `DEPLOY_APP_PRIVATE_KEY` — the App's private key

GHCR push itself uses the built-in `GITHUB_TOKEN` (`permissions: packages: write`).

## Useful links
* [Writing great Git commit messages](http://chris.beams.io/posts/git-commit/)
* [Git crash course](http://git.or.cz/course/svn.html)


## Coding standards
* [PSR-1](http://www.php-fig.org/psr/psr-1/)
* [PSR-2](http://www.php-fig.org/psr/psr-2/)

## Before you push

CI runs the same Makefile targets you can run locally, inside the devcontainer or the php container
(`docker compose exec php make check`):

| Command | What it runs | Time |
|---|---|---|
| `make check` | php-cs-fixer, phpcs, PHPStan, phpcpd, YAML lint, Doctrine schema, and `make security` | about 20 seconds |
| `make test` | PHPUnit with coverage, Infection, then the integration tests (need the `bewelcome_test` database) | about 30 seconds |
| `make security` | `composer audit`, `bun run audit` (known vulnerabilities in the locked packages; needs the network) | seconds |
| `make phpci` | all of the above and phploc, like CI | about a minute |

Style problems are fixed with `make phpcsfix` (run it twice in a row).

Git hooks do most of this for you. The devcontainer turns them on; elsewhere run `make hooks` once.
On commit, staged PHP files in `src/` are fixed with php-cs-fixer and checked with phpcs. On push, `make check` runs.
Skip a hook once with `--no-verify`.
