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

1. :balloon: [Set up you local development enviroment](INSTALL.md) and fork the repository on Github.
2. :mag: Pick a [good starter issue](https://github.com/BeWelcome/rox/labels/good%20starter%20issue)
3. :sparkles: Create a [pull request](https://opensource.guide/how-to-contribute/#opening-a-pull-request) and `@mention` the people from the issue to review
4. :sun_with_face: Fix the remaining things during review
4. :tada: Wait for it being merged!

You probably want to get started by checking out the code in `src/`.

`build/` is deprecated and the code needs to be rewritten in `src/`.

To make changes in **Javascript** bear in mind that the Webpack needs to process each change before it reflects on the site.
It is a good idea to run `bun encore dev --watch` which will keep updating files as you keep saving them.

## Documentation

Documentation is [in the doc tree](doc/book/) and can be compiled using
[mkdocs](http://www.mkdocs.org):

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
bun i --frozen-lockfile
```

everytime you see a change in either ```package.json``` or ```bun.lock```.

If any ```.scss``` file or a file in ```assets/``` changed a ```make build``` is necessary.

## Production image & deployment :rocket:

The production image is **built in CI, not on the deploy server**. The deploy host
only runs `docker compose pull && docker compose up -d` — there is no on-server
`docker compose ... --build` and no `composer install` on the server.

### How the image is built

The [`build-image`](.github/workflows/build-image.yml) workflow builds the
production `bewelcome_php` target from the [`Dockerfile`](Dockerfile) and pushes a
multi-arch (`linux/amd64` + `linux/arm64`) manifest to `ghcr.io/bewelcome/rox`. Each
architecture is built on a native runner (no QEMU) and pushed by digest, then a
merge job stitches the digests into one tagged manifest list so `docker pull`
resolves the right arch automatically. The image is self-contained: `vendor/` and
the compiled front-end assets (`public/build/`) are baked in at build time, so it
boots with no host checkout and no bind-mount of the source tree.

It runs:

* on every push to `develop` that touches image-affecting paths (`Dockerfile`,
  `src/**`, `composer.lock`, `assets/**`, etc.), and
* on every `v*` tag push.

Tags are produced by `docker/metadata-action`:

| Tag | Meaning |
| --- | --- |
| `sha-<shortsha>` | Immutable per-commit pin — **use this to deploy and roll back** |
| `develop` | Moving pointer to the latest `develop` build (convenience only) |
| `vX.Y.Z`, `X.Y`, `X` | SemVer tags, published only for `v*` tag pushes |

After a successful push **on `develop`**, the workflow sends a `repository_dispatch`
(`event_type: rox-image-pushed`) to `BeWelcome/sysadmins-infra`, which deploys the
new `sha-<shortsha>` image to **stage** automatically. The dispatch is *not* sent for
`v*` tags — production releases are triggered manually/gated from `sysadmins-infra`.

### Cut a production release

```bash
git tag vX.Y.Z
git push --tags
```

This publishes the SemVer image tags. Promote it to production from the
`sysadmins-infra` deploy workflow.

### Roll back

Redeploy a previous immutable tag from `sysadmins-infra` by pointing the deploy at
an earlier `ghcr.io/bewelcome/rox:sha-<shortsha>` (the digest is recorded in the
dispatch payload, so the rollback target is exact).

### Required secret

The cross-repo dispatch needs a token with write access to `sysadmins-infra`, stored
in this repo as the `SYSADMINS_INFRA_DISPATCH_TOKEN` secret (a fine-grained PAT or
GitHub App token). GHCR push itself uses the built-in `GITHUB_TOKEN`.

## Useful links
* [Writing great Git commit messages](http://chris.beams.io/posts/git-commit/)
* [Git crash course](http://git.or.cz/course/svn.html)


## Coding standards
* [PSR-1](http://www.php-fig.org/psr/psr-1/)
* [PSR-2](http://www.php-fig.org/psr/psr-2/)

To ensure coding standards are followed run ```make``` everytime before you commit. Fixing coding standard issues can be achieved with

```bash
make phpcsfix
```

twice in a row.
