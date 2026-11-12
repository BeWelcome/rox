# Background jobs

Rox has no message broker. Work that shouldn't happen during a request is written to a database table
and processed by a console command. The commands are started by the scheduler of the servers
(configured in `sysadmins-infra`); the application itself has no schedule.

## Commands

| Command | Purpose |
| --- | --- |
| `send:notifications` | Sends queued forum notifications (`posts_notificationqueue`) as e-mails and browser push, in batches |
| `send:massmail` | Sends the next batch of a newsletter or mass mail |
| `trips:notifications:send daily\|weekly\|monthly` | Sends trip notification mails to members who chose that frequency in their preferences |
| `comments:send:reminder` | Reminds members to leave a comment after a stay |
| `members:remind:login` | Reminds members who haven't logged in for a long time |
| `members:suspend:notification` | Suspends members away for five years despite reminders; schedules earlier ones for deletion |
| `members:remove:notconfirmed` | Removes sign-ups that never confirmed their e-mail address |
| `data:retention` | Removes the data of retired members (at least daily) |
| `browser-push:retention` | Removes expired push notifications and inactive subscriptions |
| `forum:lock-edit` | Ends editing of posts older than 30 minutes that got a reply |
| `statistics:update` | Stores today's statistics |
| `sitemap:generate` | Writes `public/sitemap.xml` |
| `geonames:update` / `geonames:update:daily` | Full import and daily changes of geonames.org places |
| `manticore:indices:forum` / `manticore:indices:geonames` | Builds the search indices |
| `translations:add:missing` | Adds missing translation codes to the database |
| `test:database:create` | Creates and seeds a local database (development only) |

## Forum notifications

```mermaid
sequenceDiagram
    autonumber
    participant M as Member
    participant C as ForumWritingController
    participant FM as ForumModel
    participant DB as MariaDB
    participant IX as ForumSearchIndex
    participant S as Scheduler
    participant N as send:notifications
    participant ML as Mailer
    participant BP as BrowserNotificationService

    M->>C: POST reply
    C->>FM: add post
    FM->>DB: forum_post
    FM->>DB: one PostNotification per subscriber
    FM->>IX: index post (Manticore)
    C-->>M: redirect to thread
    S->>N: every few minutes
    N->>DB: next batch of scheduled notifications
    loop each notification
        N->>ML: e-mail (Twig template, member's language)
        N->>BP: browser push, if the member enabled it
        N->>DB: mark as sent / failed
    end
```

The same shape (table as queue, command as worker, batch size from an environment variable) is used
by mass mail (`massmail_batch_size`) and trip notifications.

## Mail

`App\Service\Mailer` builds every mail from a Twig template in `templates/emails/`, rendered in the
receiver's language, and sends it with Symfony Mailer (`MAILER_DSN`). Sender addresses per purpose
(messages, groups, password, sign-up, newsletter, ...) come from `MAILER_*` environment variables.
`templates/emails/base.html.twig` inlines the `email` stylesheet (Foundation for Emails) with
`inline_css` (`twig/cssinliner-extra`).

## Browser push

```mermaid
flowchart LR
    action[Action in a request<br/>new message, request, reply] --> svc[BrowserNotificationService]
    svc --> row[(browser_push_notification)]
    svc --> after[BrowserPushAfterResponse]
    after -- kernel.terminate --> proc[BrowserPushNotificationProcessor]
    cmd[send:notifications] --> proc
    proc --> gw[PushGateway<br/>minishlink/web-push, VAPID]
    gw --> ext[Push service of the browser]
    ext --> sw[service-worker.js]
    proc --> del[(browser_push_notification_delivery)]
```

- Members subscribe per browser (`BrowserPushSubscriptionController`); the subscription endpoint is
  checked by `BrowserPushEndpointValidator` and `DnsBrowserPushEndpointResolver` so the server only
  contacts public push services.
- What a member wants pushed is stored as preferences (`BrowserPushPreferenceService`).
- Notifications from a request are sent after the response; anything left over is picked up by
  `send:notifications`. Gone subscriptions are removed; `browser-push:retention` cleans up old rows.
