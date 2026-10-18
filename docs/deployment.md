# Deploying Dot.Plug

Target: `https://plug.infodot.co.za`, on the shared cPanel host that also runs the other Dot apps (InfoDot, Dot.Agents and the rest). Everything goes through GitHub Actions, run by hand.

**Status when this was written: nothing is set up.** There is no host, no DNS record and no repository secret for Dot.Plug, and none of these workflows has ever been run against a host (they were checked with `actionlint`, `bash -n` and a local simulation of the crontab block only). They are inert until the checklist below is done: the first job of `deploy.yml` stops with the names of the missing secrets before anything runs.

| Workflow | Runs | What it does |
|---|---|---|
| `ci.yml` | every PR and push to `main`; called by `deploy.yml` | The PHPUnit suite. It is the release gate. |
| `drift.yml` | manual, **read-only** | Compares every tracked file of `main` with the host's copy (sha256) and prints counts and paths. Run it before the takeover. |
| `deploy.yml` | manual, `confirm=adopt` once, then `confirm=deploy` | See "First deploy" and the header of the file. |
| `server-logs.yml` | manual, read-only | Tails the newest Laravel log and the last output of each cron line. |

## Prerequisites checklist

Do these in order. The pipeline updates a live install; it does not create the host, the database or the `.env`.

### Hosting

- [ ] **DNS**: a record for `plug.infodot.co.za` pointing at the cPanel host.
- [ ] **cPanel subdomain** `plug.infodot.co.za` with its document root (`~/plug.infodot.co.za` by default) and an issued TLS certificate (AutoSSL), so `https://plug.infodot.co.za` answers.
- [ ] **Layout like the other Dot apps**: the Laravel app in a folder under the document root, with the web server serving its `public/` the way the other apps' folders do. The pipeline finds the app as the one `artisan` within two levels of `~/plug.infodot.co.za` (set the secret `DEPLOY_PATH` to skip the search). If the document root has its own `build/` folder the pipeline mirrors the new assets into it; if it has none it links one to `<app>/public/build`.
- [ ] **PHP 8.3 or newer** for the subdomain (`composer.json` requires `^8.3`; CI runs 8.4) with at least `mbstring`, `intl`, `bcmath` and `curl` (the extensions CI installs) and `pdo_pgsql` (the production database). The `php` found by a non-interactive SSH session must be that version.
- [ ] On the host: `git`, `composer`, `rsync`, `tar`, `flock` and `crontab`. There is **no Node** on the host, by design: the frontend is built on the runner and synced.
- [ ] **A deploy SSH key pair**: the public half authorised for the cPanel account, the private half stored as `DEPLOY_SSH_KEY`. `DEPLOY_KNOWN_HOSTS` is the host's `ssh-keyscan` line; confirm its fingerprint with the host provider rather than trusting the scan.

### Database

- [ ] A **PostgreSQL** database and role created in cPanel. `.env.example` defaults to `DB_DATABASE=infodot` and `DB_USERNAME=postgres`: replace them with the real names. The rollback copy does **not** contain a Postgres database, so take your own `pg_dump` before the first migration.

### The host `.env`

The pipeline requires `<app>/.env` to exist and never overwrites it. Create it from `.env.example` and set at least the values below. On every run the pipeline forces `APP_DEBUG=false` and `APP_ENV=production`, and warns about an `http://` `APP_URL`.

| Variable | Value | Why |
|---|---|---|
| `APP_KEY` | `base64:` plus 32 random bytes, once (`php -r 'echo "base64:".base64_encode(random_bytes(32)), PHP_EOL;'`) | Generate it **before** the first run, because no `vendor/` exists yet to run `key:generate`. Never change it afterwards: it signs everyone out and breaks anything encrypted. |
| `APP_URL` | `https://plug.infodot.co.za` | `.env.example` carries `http://plug.infodot.app`, a dead domain over plain http. |
| `DB_CONNECTION=pgsql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | the cPanel database | See above. |
| `SESSION_DRIVER`, `CACHE_STORE`, `QUEUE_CONNECTION` | `database` (the `.env.example` values) | Their tables come from the migrations. The deploy clears caches with targeted commands, never `optimize:clear`, because that would empty the database cache store. |
| `MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS` | the SMTP account used by the other Dot apps | `.env.example` says `MAIL_MAILER=log`, which silently writes every email to the log file. |
| `LOG_LEVEL` | `warning` | `.env.example` says `debug`. |
| `BROADCAST_CONNECTION` | `log` | No Reverb server runs on this host (see "Known limits"). Nothing in this app broadcasts today; `.env.example` still says `reverb`. |
| `INFODOT_REDEEM_URL` | `https://infodot.co.za/api/ecosystem/redeem` | Where this app redeems the one-time sign-in token InfoDot hands over. |
| `INFODOT_PLATFORM_KEY` | `plug` | This app's key in InfoDot's registry. |
| `INFODOT_SERVICE_SECRET` | a long random value, the **same** as `ECOSYSTEM_SECRET_PLUG` in InfoDot's `.env` | Proves who is redeeming. Never commit it; never print it. Without it people cannot sign in through InfoDot. |
| `INFODOT_REDEEM_TIMEOUT`, `INFODOT_PROVISION_USERS` | `5`, `true` | `false` stops InfoDot creating accounts here; people who already have one still sign in. |
| `DOT_INFODOT_URL` and the other `DOT_*_URL` | at least `DOT_INFODOT_URL=https://infodot.co.za` | `config/ecosystem.php` defaults them to the dead `https://*.infodot.app` addresses; they are the targets of the platform-switcher links. |
| `NOTIFY_*` | not needed | No `NOTIFY_*` variable is read by this app on `main` or on any open PR. |
| `ANTHROPIC_API_KEY`, `ANTHROPIC_MODEL` | leave blank | Present in `config/services.php` but not used by any code on `main`. |

### InfoDot (the sign-in hub)

- [ ] **`ECOSYSTEM_SECRET_PLUG`** in InfoDot's `.env`, equal to `INFODOT_SERVICE_SECRET` above.
- [ ] **`'plug'` in the `service_secrets` list** of InfoDot's `config/ecosystem.php`. Today the list does not name it, so InfoDot refuses this app's redeem call. InfoDot PR #32 ("Redeem: allow service secrets for the 14 remaining platforms") was open when this was written; check that it has been merged and deployed.
- [ ] **Mark the platform live**: set `'active' => true` for `'plug'` in InfoDot's `config/ecosystem.php` (it is `false` today), but only once the app really answers, so no one is sent to a dead address. InfoDot's `DOT_PLUG_URL` already defaults to `https://plug.infodot.co.za`.
- [ ] `ECOSYSTEM_TRUSTED_DOMAINS` in InfoDot keeps its default `infodot.co.za`, which covers `plug.infodot.co.za`.

### Repository secrets (Settings, Secrets and variables, Actions)

| Secret | Value |
|---|---|
| `DEPLOY_HOST` | `plug.infodot.co.za` |
| `DEPLOY_USER` | the cPanel account user (the one the other Dot apps deploy as) |
| `DEPLOY_SSH_KEY` | the private deploy key |
| `DEPLOY_KNOWN_HOSTS` | the host's `known_hosts` line |
| `DEPLOY_PATH` | optional: the absolute app folder, to skip the search for `artisan` |

## First deploy

1. Finish the checklist. A brand-new, empty subdomain needs a first copy of the code and a `.env` before the pipeline can take over: `git clone https://github.com/sakhilebhayi/Dot.Plug.git` into a folder under the document root (the repository is public), then create `.env` as above. (An empty folder fails the run before maintenance mode, because there is no `artisan` or `.env` to work with.)
2. Run **Drift Report** and read it. For a fresh clone of `main` it says the host already matches.
3. Run **Deploy to Production** with `confirm=adopt`. It writes a rollback copy, makes the folder a checkout of `main` without deleting anything untracked (`.env`, `storage/`), installs dependencies, ships the build, forces the production settings, **lists** the pending migrations without running them, rebuilds the caches, and checks the site. Add `migrate=migrate` only on a database you do not mind migrating at once (a brand-new empty one is fine).
4. Take a `pg_dump`, read the pending-migrations list in the log, then run it again with `confirm=deploy`, which migrates.
5. Check the log (see "Reading the log"), open `https://plug.infodot.co.za/login` and sign in through InfoDot.
6. Only then mark the platform live in InfoDot.

Every release after that: **Deploy to Production** with `confirm=deploy` (`gh workflow run deploy.yml -f confirm=deploy`). The CI gate runs first, then the frontend is built, then the host steps run.

A failure **before** maintenance mode (the gate, the build, ssh, the upload, the rollback copy) leaves the live site on the previous release. A failure **after** it starts leaves the site **down on purpose**, in maintenance mode, with the rollback copy's name in the log: read the log, fix the cause and run the same workflow again; it brings the site back up itself. It is never brought back automatically, because the Dot.Agents pipeline's automatic rollback once fired after failures that never touched the server.

## Cron and queue lines

**None.** Dot.Plug has no scheduled command (`routes/console.php` only defines `inspire`) and nothing queued (its one notification, `ExtensionCertifiedNotification`, is sent synchronously), on `main` or on any open PR. The pipeline therefore does not touch the shared crontab at all. When a scheduled command or a queued job is added, copy the "Install the scheduler cron line" step from another Dot app's `deploy.yml` (for example Dot.Tutor's) and set the marker to `# dot-plug`.

## Rollback

- Every run writes `~/dot-plug-<mode>-<UTC time>.tgz` (the app folder without `vendor/`, `node_modules/`, logs and the build); the newest three are kept. To restore: stop writes (`php artisan down`), `tar xzf` it over the app folder, run `composer install --no-dev`, redeploy to rebuild the frontend, then `php artisan up`.
- **Code**: the pipeline always deploys `origin/main`. To go back, revert the change on `main` and run `confirm=deploy`. The log prints `== Current release: <sha>` before and `== Deployed: <sha>` after.
- **Database**: migrations are not rolled back, and the copy holds no Postgres data. Restore from your own `pg_dump`.
- On `adopt`, host-only commits survive on a local branch `host-before-adopt-<time>`, and host-only migration files are moved to `~/dot-plug-quarantine-<time>`, never deleted.

## Reading the log

- The Actions run log shows the steps in order: the input and secret check, the CI gate, the build, "Locate the app", the staged upload, "Deploy the code" and the smoke check. Lines starting `==` are the host script's progress: `== Rollback copy`, `== Current release`, `== Code`, `== Pending migrations`, `== Deployed`, `== Smoke check passed.` An `::error::` line names the cause and says whether the site is still up.
- **Server Logs** (`server-logs.yml`) prints the last 120 lines of the newest `storage/logs/laravel*.log` and the last 10 lines of each `storage/logs/cron-*.log`. It is a fixed command, never a shell.
- The smoke check fetches `/`, `/login`, `/up`, every built entry file from `/build/...`, and sends one harmless `POST /up` (a 405) to prove no debug page is served.

## Known limits

- **Never run `php artisan db:seed` in production**: `DatabaseSeeder` creates `test@example.com` with a known password. There is no reference data to seed.
- **Reverb cannot run under shared cPanel** (a long-running WebSocket process). The pipeline never starts, restarts or reloads it. Nothing in Dot.Plug broadcasts today (`laravel/reverb` is installed but unused), so nothing is lost. A realtime feature added later will not update live until Reverb runs somewhere else.
- The signed-in pages load Tailwind from a CDN at runtime and Alpine.js from unpkg. The smoke check only fetches the guest pages and the built assets, so a CDN problem or a signed-in-only error is not caught by it.
- The first deploy after a long gap migrates everything pending in one go; read the pending list from the `adopt` run first.
- The rollback copy includes `storage/app` (uploads and other files) and `.env`; check the account's disk quota, three copies are kept.
- No backups, no scheduler probe and no Dot.Brain guardian endpoint exist for this app yet.
- `INFODOT_*` redeem only works once InfoDot lists this platform (checklist above).
