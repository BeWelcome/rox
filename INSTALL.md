# Getting started with Rox development

There are three ways to run a local Rox development environment, listed from easiest to most involved.

## A) VS Code DevContainer (recommended)

The repository ships a `.devcontainer` configuration that sets up the full stack — PHP 8.2, Nginx, MariaDB, Manticore Search, and Mailpit — automatically inside a container. No manual dependency installation needed.

### Requirements

- [VS Code](https://code.visualstudio.com/) with the [Dev Containers extension](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.remote-containers)
- [Docker](https://docs.docker.com/get-docker/) (Docker Desktop, Rancher Desktop, or equivalent)

### Start

1. Clone the repository and open it in VS Code:

    ```bash
    git clone https://github.com/BeWelcome/rox.git
    code rox
    ```

2. VS Code will detect the `.devcontainer` configuration and prompt **"Reopen in Container"** — click it. The first run builds the images and imports seed data; expect 5–10 minutes.

3. Once the container is ready, VS Code opens a browser tab at `http://localhost` automatically. Log in with username `member-2` and password `password`.

### GitHub Codespaces

You can also run the devcontainer in the cloud without installing anything locally: open the repository on GitHub and choose **Code → Codespaces → Create codespace on master**.

### Accessing services

| Service | URL |
|---|---|
| BeWelcome app | `http://localhost` |
| Mailpit (catch-all mailer) | `http://localhost:1080` |
| Manticore HTTP API | `http://localhost:9308` |

### macOS + Rancher Desktop note

On macOS with Rancher Desktop, `forwardPorts` in `devcontainer.json` can cause VS Code to intercept connections before Rancher's port tunnel reaches the container, resulting in pages that hang indefinitely. If you hit this, remove the `forwardPorts` key from `.devcontainer/devcontainer.json` and rebuild the container — VS Code auto-detects host-mapped ports from the compose file anyway. This does not affect WSL or Docker Desktop users.

---

## B) Docker Compose directly

If you prefer not to use VS Code, you can bring the stack up manually.

### Requirements

- [Docker](https://docs.docker.com/get-docker/)
- [Docker Compose](https://docs.docker.com/compose/install/) (v2)

### Start

1. Clone the repository:

    ```bash
    git clone https://github.com/BeWelcome/rox.git
    cd rox
    ```

2. Copy the override template and bring everything up:

    ```bash
    cp docker-compose.override.yml.dist docker-compose.override.yml
    docker compose up -d
    ```

   The first run downloads images and imports seed data. Wait until `docker compose logs -f php` shows `ready to handle connections`.

3. Open `http://localhost` in your browser. Log in with `member-2` / `password`.

> **Windows users:** place the repository inside the WSL filesystem (not under a Windows drive path) to avoid volume-mount performance issues. Run all commands from a WSL terminal.

### Useful Makefile targets

```bash
make build          # compile CSS and JS assets
make install        # first-time setup (runs docker compose up + asset build)
make phpcsfix       # auto-fix PHP coding standard issues
```

---

## C) Manual installation (advanced)

For contributors who need to debug at the OS level or cannot use Docker.

Tested on Debian/Ubuntu. Adapt paths and commands for other distributions.

### Requirements

- PHP 8.2 with extensions: `mbstring`, `xml`, `fileinfo`, `intl`, `xsl`, `gd`, `apcu`
- MariaDB >= 10.5
- [Manticore Search](https://manticoresearch.com/) 6.x
- [Composer](https://getcomposer.org/) v2
- [Node.js](https://nodejs.org/) LTS + [Yarn](https://classic.yarnpkg.com/)
- An SMTP relay (e.g. Mailpit, Postfix, or any local MTA)

### Initialize

1. Install PHP dependencies:

    ```bash
    composer install
    ```

2. Copy `.env` to `.env.local` and set `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`, and `MAILER_DSN` to match your local setup.

3. Create and seed the database:

    ```bash
    php bin/console test:database:create --drop --force
    ```

4. (Optional) Import language and translation data:

    ```bash
    wget https://downloads.bewelcome.org/for_developers/rox_test_db/languages.sql.bz2
    wget https://downloads.bewelcome.org/for_developers/rox_test_db/words.sql.bz2
    bunzip2 languages.sql.bz2 words.sql.bz2
    mysql bewelcome -u bewelcome -pbewelcome < languages.sql
    mysql bewelcome -u bewelcome -pbewelcome < words.sql
    ```

5. Install JS dependencies and compile assets:

    ```bash
    yarn install --frozen-lock
    make build
    ```

6. Start the development server:

    ```bash
    symfony serve
    ```

   Access the site at `https://localhost:8000/`. Log in with `member-2` / `password`.

---

## After any update

When `composer.json` or `composer.lock` changes:

```bash
composer install --prefer-dist --no-progress --no-interaction --no-scripts
```

When `package.json` or `yarn.lock` changes:

```bash
yarn install --frozen-lock
```

When any `.scss` file or file in `assets/` changes:

```bash
make build
```

When you want to clear the Symfony cache:

```bash
php bin/console cache:clear
```
