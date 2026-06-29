# Deployment Runbook — Hostinger Shared Hosting (Premium/basic plan, no SSH)

This plan does not expose a terminal. Every `composer`/`npm`/`artisan` command runs
**locally**, and only the resulting files are uploaded to the server.

## 1. Build locally

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

This produces a `vendor/` directory and a built `public/build/` (Vite manifest +
hashed assets). Both must be uploaded — the server never runs `composer install`
or `npm run build` itself.

## 2. Deploy

Two options, in order of preference:

- **hPanel Git deploy** (if available on the account): connect the repository in
  hPanel → Advanced → Git, and configure it to deploy on push. Since there is no
  SSH, there is no post-pull hook to run `composer install`/`npm run build` on the
  server — commit the built `vendor/` and `public/build/` directories to the
  deploy branch, or upload them separately after a Git deploy (confirm which is
  actually supported once the account is provisioned; see open question in the
  PRD §9).
- **FTP / hPanel File Manager upload** (fallback, always available): zip the
  project (including `vendor/` and `public/build/`, excluding `node_modules/` and
  `.git/`) and upload/extract via File Manager, or upload via FTP client.

## 3. One-time / per-release server steps

Since there is no SSH to run `artisan` commands on the server, any command that
would normally run post-deploy (`migrate`, `config:cache`, `key:generate`, etc.)
must be triggered another way. Until the account's actual capabilities are
confirmed (§9 of the PRD), use a locked-down, single-use web route as the
mechanism:

1. Add a temporary route (behind a secret token in the URL, e.g.
   `/deploy/migrate/{token}`) that runs `Artisan::call('migrate', ['--force' => true])`.
2. Hit that URL once after each deploy that includes new migrations.
3. Remove or re-secure the route once a better mechanism (Git deploy hook, or
   confirmed SSH on a higher plan) is available.

Do not leave an unauthenticated artisan-executing route live permanently.

## 4. Cron job (only one needed)

In hPanel → Advanced → Cron Jobs, add:

```
* * * * * php /home/<account>/domains/<domain>/public_html/artisan schedule:run >> /dev/null 2>&1
```

Confirm the actual minimum interval Hostinger allows on this plan once
provisioned — if it's coarser than 1 minute, Laravel's scheduler still runs
correctly, just less often (everything in `routes/console.php` is designed
around a ~5 minute floor, see PRD §7).

Check the `heartbeat` log entries after the first deploy to confirm the cron job
is actually reaching `schedule:run`.

## 5. Storage

`php artisan storage:link` cannot be run on the server without SSH. Rather than
relying on the `public/storage` symlink, the `public` disk in
`config/filesystems.php` has `'serve' => true`, which makes Laravel itself
serve those files via a built-in route (`storage.public`, mounted at
`/public-storage/...`) — no symlink required. `Storage::disk('public')->url()`
already returns URLs under that prefix, so no application code needs to know
which mechanism is in play. If a later plan upgrade adds SSH and a real symlink
is created at `public/storage`, that's a different URL prefix (`/storage`,
reserved by the private `local` disk) and doesn't conflict with this route.

## 6. Rollback

Keep the previous release's `vendor/` + `public/build/` + codebase zipped before
overwriting via File Manager/FTP, so a bad deploy can be re-uploaded quickly.
