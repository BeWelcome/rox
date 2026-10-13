# RFC 0001: Upgrade the BeWelcome platform to current versions

| | |
|---|---|
| Status | Proposed, open for comments |
| Author | Neophytis (sysadmins) |
| Discussion | The pull request that adds this file. Comment on the lines you disagree with, or answer the open questions at the end. |
| Comments until | 2026-10-17 |
| Decision | Merging the PR means the order is accepted. Later changes go through a new PR on this file. |
| Tracking | #519 (tracking issue), #518 (branch rename, prerequisite), #517 (CI). Infra side: BeWelcome/sysadmins-infra#613 and BeWelcome/sysadmins-infra#543 (sysadmins repository, members only) |

## Summary

Bring the platform that runs BeWelcome on leopard to current, supported versions before we fix more code: Symfony 5.4 to 7.4 LTS, Doctrine ORM 2 to 3, PHP 8.3 to 8.4, Manticore 6.3.8 to 29.9, a clean and reproducible rox image, and the frontend libraries. The work is split into small steps, one PR and one release each, tested on leopard-stage first. This document proposes the order and asks for four decisions.

## Motivation

- **Working rule (agreed on #519, 2026-10-03): modernise first, then fix.** Fixing code on Symfony 5.4 and then fixing it again during the upgrade doubles the work. Legacy `build/` code only gets security, data loss and member-blocking fixes until its module moves to Symfony.
- **Support is running out.** Symfony 5.4 bug fixes ended 11/2024. PHP 8.3 is security-only since 12/2025. Bootstrap 4 is end of life since 01/2023. CKEditor 40 has open XSS advisories.
- **Search needs a rebuild anyway.** The geonames index has no translations (BeWelcome/sysadmins-infra#543), and Manticore 6.3.8 is far behind.

## Scope

In scope: rox code and its PHP and npm dependencies, the rox image (PHP, nginx, Node build), Manticore, MariaDB, and the leopard deploy script where the upgrade needs it.

Out of scope: monitoring (puma), other apps (Zulip, Authentik, Vaultwarden, Znuny, Mailcow), operating system upgrades. They are tracked by the sysadmins in BeWelcome/sysadmins-infra#613.

## Where we are and where we go (researched 2026-10-03)

| Component | Prod today | Target | Support of target |
|---|---|---|---|
| Symfony | 5.4.45 | 7.4 LTS, through 6.4 | 7.4: bug fixes to 11/2028, security to 11/2029. 6.4 bug fixes end 11/2026, so 6.4 is a step, not a stop |
| Doctrine | ORM 2.20.13, DBAL 2.13.9, doctrine-bundle 2.7.2, migrations-bundle 2.2.3 | ORM 3, DBAL 3.10 first, DBAL 4 later | |
| PHP | 8.3.33 | 8.4.26 now, 8.5 later | 8.4: active to 12/2026, security to 12/2028 |
| Manticore | 6.3.8, PHP client 3.2.0 | 29.9.0 pinned by digest, client 4.0.1 (4.1 when tagged) | |
| MariaDB | 12.3.3 | already current | |
| nginx (web image) | 1.30.4 | 1.30.5 | |
| Node (asset build) | nodejs 24 from apk, yarn from Alpine **edge**, both left in the prod image | pinned `node:24-alpine3.24` build stage, nothing of it in the prod image | Node 24 LTS to 04/2028 |
| Composer | unpinned installer | `COPY --from=composer:2.10.3` | |
| Frontend | Bootstrap 4.6.2, CKEditor 40, Encore 4.5, Tailwind 3.3, Font Awesome 5, React 17, jQuery 3.7 | Bootstrap 5.3, CKEditor 48, Encore 7, Tailwind 4, Font Awesome 7, React 19, jQuery 4 | `npm audit` on the lock: 3 critical, 34 high, 49 moderate |

Sources: symfony.com/releases, php.net/supported-versions, the Doctrine ORM and DBAL `UPGRADE.md` files, the Manticore changelog (manual.manticoresearch.com/Changelog), the manticoresearch-php compatibility table, getbootstrap.com/docs/5.3/migration, the CKEditor new installation methods guide, the nodejs/Release schedule.

## What we will hit in the code

Evidence is from `feature/docker-master` (prod).

1. **Annotations to attributes.** 1297 `@ORM\` in 73 files, 253 `@Route` in 53, 40 `@ParamConverter`, 38 `@Assert`, 11 `@IsGranted`, no attributes yet. ORM 3 and Symfony 7 no longer read annotations. Rector converts most of it.
2. **A hidden break in Symfony 6.4.** `config/services.yaml:397-399` passes `lock_mode: LOCK_NONE` as a string to the session handler. On 6.4 that is a TypeError on every request. `develop` (453035788) has the fix.
3. **Security layer.** The JWT api firewall uses a `guard:` authenticator (removed in 6.0), password encoders (src/Factory/EncoderFactory.php, src/EventSubscriber/AuthenticationEventSubscriber.php), `loadUserByUsername` (src/Provider/UserProvider.php:34), the old `Security` class in 6 files, and `Member implements \Serializable`. A wrong change to how `Member` is stored in the session logs every member out.
4. **Doctrine 3 removals.** `ObjectManagerAware` in 7 entities, 17 `App:Entity` aliases (one on the conversation pager), 4 partial `flush($entity)` calls, `Lexer::T_*` in 3 DQL functions, `Doctrine\Common\Persistence`.
5. **Old DBAL calls.** 36 `fetch()/fetchAll()`, 4 `Connection::query()`, 18 `$stmt->execute([...])`, custom enum and set types.
6. **Controller APIs.** 70 `getDoctrine()`, 10 `$this->get()` (including the `session` service in LegacyController), 17 `Request::get()`.
7. **Swiftmailer** (abandoned) sends the legacy mails: signup, member mails, mailbot (modules/mail/lib/mail.lib.php) and the feedback form.
8. **Bundles that need a major version.** pagerfanta 3 to 4, htmlpurifier-bundle 3 to 5, nelmio-security 2 to 3, webpack-encore-bundle 1 to 2, migrations-bundle 2 to 3, dama 6 to 8. sensio/framework-extra-bundle is abandoned and gets replaced by core attributes.
9. **Manticore.** Tables must be rebuilt, and 29 cannot be downgraded. The PHP client 3 to 4 renames `Index` to `Table` (about 10 call sites, already done on `develop`). Since 7.0, Thai is no longer part of `ngram_chars: cjk`, so our index commands must use `cont` or Thai text stops being searchable. Our deploy script would upgrade Manticore in place with only a 10 second stop, and its health check ignores search.
10. **The image is not built strictly from the lock.** `yarn install --frozen-lock` is a typo that yarn ignores (Dockerfile, docker-entrypoint.sh, CI). The image also mixes the Alpine edge repository into 3.24.
11. **Frontend.** Bootstrap 5 touches 133 `data-toggle` in 62 files, 239 `ml-/mr-` in 93 files, 173 `form-group` and more, including legacy `build/` templates. jQuery 4 waits for the Bootstrap 4 plugins to go (select2, tempusdominus, ekko-lightbox, rangeslider.js). Tailwind 4 rewrites every `u-x` class to `u:x` (664 lines).
12. **Few tests.** About 14 test files for about 160,000 lines. Stage testing and the smoke suite do most of the regression checking.

Good news: legacy `build/` code talks to the database through its own mysqli layer, not Doctrine, so the Doctrine upgrade barely touches it. `develop` already solved many of these problems; we use its commits as a reference per file, not as a merge.

## Proposed order

Every step is one PR and one release: deploy to leopard-stage, run the smoke suite plus the step's own checks, tag a release, deploy with `deploy-prod`. Rollback is the previous image tag unless the step says otherwise.

```
Step 0 groundwork ──┬── Step 1 search (Manticore), any time after step 0
                    ├── Step 2 preparation on Symfony 5.4 (2a to 2f, in order)
                    │        └── Step 3 Symfony 6.4 ── Step 4 PHP 8.4 ── Step 5 ORM 3 ── Step 6 Symfony 7.4
                    └── Frontend: F1 and F2 any time after step 0; F3 and F4 after step 6
```

### Step 0: groundwork, no behaviour change
- **0a.** #518: `feature/docker-master` becomes `main`, protected.
- **0b.** Safety net: key pages (legacy and Symfony: login, signup, profile, messages, search, forum, groups, admin) in the smoke suite; `lint:container` and `lint:twig` in CI; a deprecation baseline; PHPUnit green (the part of #517 needed now).
- **0c.** Remove dead code and about 15 unused packages, including npm `tar` with a critical CVE.
- **0d.** Clean image: strict lock install, pinned Node 24 build stage, no Alpine edge, pinned Composer, PHP 8.3.35, nginx 1.30.5; CI on Node 24 and MariaDB 12.3.

Why first: less code and fewer dependencies to upgrade, a check that catches regressions, and an image we can rebuild exactly.

### Step 1: search (can run alongside step 2)
- **1a.** sysadmins-infra PR first: a longer stop period for Manticore, the deploy does not recreate Manticore unless asked, and the health check covers search.
- **1b.** rox PR: Manticore 29.9.0 pinned by digest, client 4.0.1, `ngram_chars: cont`.
- **1c.** Stage, then prod in a quiet hour: back up the old index data, build both tables on a second Manticore container (geonames about 20 to 30 minutes, forum under 2), compare counts, switch over, test search in several languages and the forum as member and moderator. Fixes BeWelcome/sysadmins-infra#543.
- Rollback: restore the backed-up 6.3.8 data and the previous rox tag.

### Step 2: preparation, still on Symfony 5.4 (one release each)
- **2a.** Session handler fix; remove the unused JWT api firewall.
- **2b.** Security layer. Stage check: existing logins and "remember me" survive the deploy.
- **2c.** Swiftmailer to symfony/mailer. Stage check: signup mail, password reset, message notification, feedback form, mailbot.
- **2d.** Controllers use injection instead of `getDoctrine()` and `$this->get()`.
- **2e.** Annotations to attributes with Rector; remove sensio-framework-extra-bundle. Check: the route list and the Doctrine mapping are identical before and after.
- **2f.** Doctrine preparation: DBAL 3.10, migrations-bundle 3, all old DBAL calls, the ORM 3 removals, and the correct `server_version` (the config says MariaDB 10.1.41, prod runs 12.3.3).

Why here: Symfony 5.4 reports every deprecation that 6.0 removes, so each fix ships on a working version in a small, reversible release.

### Step 3: Symfony 6.4
Bundle major versions and recipe updates. We go on to step 6 without a long stop, because 6.4 bug fixes end 11/2026.

### Step 4: PHP 8.4
New base image, composer platform 8.4, Rector `php84` set.

### Step 5: Doctrine ORM 3
Check that the mapping still matches the prod schema (`doctrine:schema:update --dump-sql` is empty).

### Step 6: Symfony 7.4 LTS
Remaining deprecations to zero. Target reached: rox code fixes (for example #545 and #546) can start.

### Frontend track
- **F1** (after step 0): CKEditor 48, which clears the XSS advisories.
- **F2** (after step 0): Encore 5, workbox 7, chart.js 4, React 19.
- **F3** (after step 6): Encore 7, Tailwind 4, Sass `@use`, Font Awesome 7.
- **F4** (after decision 1): Bootstrap 5 and its plugins, then jQuery 4.

### Later
DBAL 4, PHP 8.5, Carbon 3.

## Alternatives considered

- **Merge `develop`.** It is already on Symfony 8.1, ORM 3 and PHP 8.4, but it is 283 commits apart from prod, mixes upgrades with features and schema changes, and switched to FrankenPHP. One giant merge cannot be tested or rolled back in pieces. We use it as a reference instead.
- **Stay on Symfony 5.4 until its security support ends (02/2029).** No bug fixes, and every newer bundle assumes 6.4 or later. Each code fix made now would be made twice.
- **Stop on Symfony 6.4.** Its bug fixes end 11/2026, so we would have to move again within weeks.
- **Upgrade Manticore in place.** Version 7.0 changed the binlog format and needs a clean shutdown. Rebuilding is safer, and BeWelcome/sysadmins-infra#543 needs a rebuild anyway.

## Risks

- **Few tests.** Mitigation: step 0b and the per-step stage checks.
- **Members logged out** by the session change (2b). Mitigation: test with existing sessions on stage.
- **Mail stops** after the mailer change (2c). Mitigation: send every mail type on stage.
- **Search cannot go back** after Manticore 29 writes its data. Mitigation: backup of the old data before switching.
- **Volunteer time.** The steps are small, so work can pause between releases without leaving prod half-upgraded.

## Open questions (please comment)

1. **Bootstrap 5:** migrate all templates now, or only the Symfony templates now and each legacy module when it moves to Symfony? Proposal: the second, following the modernise-first rule.
2. **Package manager:** stay on yarn 1 with a strict lock, move to npm, or follow `develop` to bun?
3. **Who does what:** proposal: sysadmins take 0d, step 1 and F1; rox developers take steps 2 to 6.
4. **Manticore port:** keep 9312, or move to 9308 as `develop` did?

## Rollout checklist
- [ ] Step 0a #518 branch rename
- [ ] Step 0b safety net
- [ ] Step 0c dead code and unused packages
- [ ] Step 0d clean image
- [ ] Step 1a deploy script ready for Manticore
- [ ] Step 1b and 1c Manticore 29.9 on stage, then prod
- [ ] Step 2a session handler and JWT removal
- [ ] Step 2b security layer
- [ ] Step 2c symfony/mailer
- [ ] Step 2d controller APIs
- [ ] Step 2e annotations to attributes
- [ ] Step 2f Doctrine preparation
- [ ] Step 3 Symfony 6.4
- [ ] Step 4 PHP 8.4
- [ ] Step 5 Doctrine ORM 3
- [ ] Step 6 Symfony 7.4 LTS
- [ ] F1 CKEditor 48
- [ ] F2 Encore 5 and small frontend upgrades
- [ ] F3 Encore 7, Tailwind 4, Sass, Font Awesome 7
- [ ] F4 Bootstrap 5 and jQuery 4
