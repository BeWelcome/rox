# Changelog

What changes for members and volunteers from one release to the next. Newest release first.
The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/); releases are tagged `vX.Y.Z`.

## [Unreleased]: 4.0.0 (route C)

Compared with 3.8.5, the version running on bewelcome.org. Try it on stage.bewelcome.org.

### New

- **Sign-up in two steps.** First username, e-mail address and password; then the profile: name, mother tongue, birth
  date, gender, location and whether you can host. The second step opens right after the first, through the
  confirmation link, or when you log in before you finished it. Try /signup.
- **Password strength meter** while you type a new password (sign-up and change password).
- **Show or hide the password** with the eye button on the login forms.
- **A yellow banner** on every page while your e-mail address is not confirmed yet.
- **Your language choice is remembered** for the next visit and for e-mails.

### Changed on purpose: please tell us how this works for you

These come from the new code base (develop). They are kept for now; your feedback decides.

- **Before the e-mail address is confirmed**, members cannot send messages or requests, invite, write comments, notes
  or relations, or create groups, trips or forum topics. 3.8.5 allowed this.
- **Password reset at most once every 5 days.** The link in the mail still works for 2 days.
- **Change password** has no "repeat password" field any more, and needs at least 8 characters.
- **Logging in no longer resets the "reminders without login" counter** (3.8.5 set it back to 100 at each login).
  Members are still set back to active when they log in, as on 3.8.5.
- **Edit my profile** accepts an empty last name.
- **Editing a comment** is refused whenever editing is switched off for it (3.8.5: only for negative comments).
- **The thread's bottom reply button** follows the same rules as the top one (group members only, moderators only).
- **Feedback form:** the thank-you line moved from the page into a message at the top.
- **The text editor** (forum, messages, comments) has a new look (CKEditor 47); icons are slightly redrawn
  (Font Awesome 6).
- **Treasurer, add donation:** a donation without a member is stored without a member instead of member 0.

### Fixed during review: the same as 3.8.5 again, please check

Route C first differed from 3.8.5 here; these now work as on bewelcome.org.

- Login form protected against cross-site requests again, and you land back on the page you came from.
- Everyone who could log in on 3.8.5 can log in again (members marked inactive by choice, "need more", hidden or
  pending were locked out).
- "Last login" is updated again when you log in, and logging in reactivates a member again.
- Password reset with an unknown username or e-mail shows the error message instead of an error page.
- Forms check their fields again: create a group (name, description, picture), edit a photo, translations, FAQ,
  report a comment, community news comments, admin "change username".
- Translators can add the first translation of a text again.
- Sign-up step 2 only opens for the person who created the account. The hidden anti-spam field and the mother tongue
  question are back, and error messages show as sentences, not as codes.
- Ukrainian interface is back.
- The language menu in the header shows its two columns again; the text editor toolbar is in your language again.
- Removing a language from your profile works again.
- Old search bookmarks keep their filters (dinner, tour, with photo, with "about me").
- "Download my data" lists your phone numbers, website, messengers and shouts again.
- Profile comments: all of them again, the most recent activity first.
- The spam check on messages catches hidden contact details again.
- The admin log filters by type and member again.
- The hosting-interest slider on "edit my profile" has its look again.
- Credits page as before (Znuny listed); no more error page when a page sends you back without knowing where from.
- Analytics on sign-up, password and error pages, and the message, request, comment and search goals.
- Nightly data purge for members who left works again and deletes only their own photos.

### Fixed: problems that 3.8.5 has

- Retiring a member no longer deletes the profile pictures of other members whose number starts with the same digits.
- The forum "unsubscribed" page no longer shows an error.
- The mail telling a member that their request to join a group was approved could not be built; it is sent again.

### Removed

- The old sign-up pages /signup/1 to /signup/4 (replaced by the new sign-up).
- /api/login and /donate/done, /donate/cancel, /donate/notify: unused (no visits in the logs).

### Known issues

- Translators' page previews (admin, translation mockups) show an error page. Same on 3.8.5.
- A relation text that contains an e-mail address or phone number is not saved, without a message.
- The group approval "access denied" text shows technical words.
- Search for logged-in members: not yet checked with a place typed but not picked from the list.

### Under the hood

PHP 8.4, Symfony 7.4, Doctrine ORM 3, Manticore 29, current versions of all libraries (none older than in 3.8.5, no
known security advisory).

## Older notes (2016)

* Separate Rox\ namespace into modules (under `module/` directory)
  * Routes are included as part of module config, but Symfony still requires a
  master routes files, which is stored in `config/`
* Added a high level level config dir at `config/`. Glob inclusion allows files
to be environment specific or gitignored for local dev.
* Added FrameworkBundle and TwigBundle
* Added Carbon for better DateTime handling. Works well with Twig, and Eloquent
has native support for it.
* Use Symfony kernel for all requests, but fallback to a legacy kernel which
wraps the older request lifecycle
* index.php updated to be more like Symfony and delegate more to the Application
and Kernel classes
* index.php sets the working directory to the project root (instead of htdocs
previously.)
* Caching home page stats and translation catalogue - much faster home loading
now
* Twig extension `Rox\Core\Extension\AssetExtension` provides an `asset`
function to swap unversioned asset URLs in templates to the versioned URLs.
* FontAwesome and Lato font are now pulled via npm and copied with Grunt. The
Signika font is not available through npm, so it is committed with the Core
module.
* Upon successful login, add $_SESSION['IdMember'] value to native Symfony
session implementation
* Added vlucas/phpdotenv for better environment config management. Currently
only has `APP_ENV` and `APP_DEBUG`. The existing rox ini file remains for now.
Copy `.env.dist` to `.env` and adjust as required.
* Faker added to dev dependencies and Twig global various. This can be used to
generate fake data when the anonymised Rox data is not sufficient for testing.
* Removed grunt-autoprefixer - the file it modifies is generated from sass
* Add various new Gruntfile tasks: eg. jshint for JS linting, uglify for JS
minification, cssmin
* Add grunt-asset-versioning which generates versioned css and js files plus a
manifest file to map for example 'styles.css' to 'styles.a0f1dd.css'
* Front end dependencies removed from Composer and added to NPM. Grunt tasks
then copy from `node_modules/` to `htdocs/`.
