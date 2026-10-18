<div align="center">

<img src="public/images/logo.png" alt="Dot.Plug" width="200" />

# Dot.Plug

</div>

Developer marketplace and extension framework for the Dot Ecosystem — third-party developers build, certify, and publish extensions (integrations, connectors, domain add-ons, vertical tools) that add capability to any Dot platform without changing that platform's core codebase.

Full architecture, domain model, and roadmap: [`wiki.md`](./wiki.md).

## Status

**MVP scaffold, hand-authored and UNVERIFIED.** This codebase was written in an environment with no PHP, Composer, or PostgreSQL available. It has never been installed, migrated, or run. Treat it as a careful first draft, not working software, until someone with a real PHP toolchain runs it and fixes what doesn't work.

## Responsive app shell

Signed-in pages render inside `resources/views/layouts/app.blade.php`. From 860px up it is the fixed 260px sidebar and top bar, as before. Below 860px the sidebar becomes an off-canvas drawer that the **Menu** button in the top bar opens (`#app-sidebar`, `[data-shell-open]`, `[data-shell-close]`). `layouts/partials/shell-script.blade.php` moves focus into the drawer, closes it on Escape or a backdrop click, locks page scroll, makes the page behind it inert and returns focus to the button. It is plain JavaScript, no Alpine or build step.

- Wide tables go in a `<div class="table-scroll" role="region" aria-label="..." tabindex="0">` so only the table scrolls sideways.
- Page markup is mostly inline styles, so the layout carries a few phone-only attribute-selector rules (page padding, grids collapse to one column under 640px, 44px controls, 16px field text so iOS does not zoom). New pages should prefer classes and `minmax()` grids over fixed widths.
- The Jetstream `navigation-menu` bar that sits under the fixed top bar is wrapped in `.shell-legacy-nav` and hidden on phones. Its team switcher and sign-out now live in the drawer footer.
- `tests/Feature/ResponsiveShellTest.php` asserts the markup hooks above. It checks the rendered HTML, not pixels, so layout still needs a look in a browser at 360, 375, 768 and 1280px.

## Stack

- Laravel 12, PHP 8.3+
- Jetstream (Livewire stack) + Fortify + Sanctum — teams, auth, API tokens
- Tailwind CSS, Vite
- PostgreSQL

## What's implemented

- Jetstream Teams application shell: registration/login, teams, profile, two-factor auth, API tokens, the ecosystem SSO handoff route (`/auth/ecosystem`), an in-app notification bell.
- Marketplace MVP:
  - `Extension`, `ExtensionVersion`, `Installation` models and migrations (team-scoped — a publisher is just a Jetstream `Team`).
  - Browse/publish/view extension listings (`/extensions`).
  - Install/uninstall an extension for the current team.
  - A dashboard showing what the current team has installed and, if it's a developer team, what it has published.
  - A seeder with a handful of example extensions.
- Feature tests covering the copied Jetstream flows plus the new marketplace flows (`tests/Feature/Plug/ExtensionMarketplaceTest.php`, `tests/Feature/DashboardTest.php`).

## What's not implemented

Capability grants, the certification pipeline, the extension runtime/sandbox, anomaly detection, reviews/moderation, payments, version diffing, and Knowledge Pack publishing to Dot.Brain. See `wiki.md` §3–§7 for the full built-vs-planned breakdown.

## Roles

Access follows the team (company) role. InfoDot is the source of truth and the role is re-applied at every sign-in.

| InfoDot role | Dot.Plug role | Can |
|---|---|---|
| owner, admin | admin (owner for the creator) | everything: install, uninstall, publish, edit and delete listings |
| editor | editor | browse the marketplace, the dashboard and extension pages; no installs or publishing (those are company-wide, so owner/admin only) |
| viewer | viewer | read only: same read surface as an editor, no write controls are shown and every write route answers 403 |
| anything else | viewer | an unrecognised InfoDot role gets the least powerful role, never editor |

Moving someone from editor or admin to viewer in InfoDot takes effect at their next sign-in. Install, uninstall and publish stay owner/admin only; the role matrix is in `tests/Feature/Plug/ViewerRoleMatrixTest.php`.

## Getting started

Not verified to work — there was no PHP/Composer available to test this. In theory, on a machine with PHP 8.3+, Composer, Node, and PostgreSQL:

```bash
composer install
cp .env.example .env
php artisan key:generate
# create the database named in .env (DB_DATABASE), then:
php artisan migrate --seed
npm install
npm run build   # or `npm run dev` for local development
php artisan serve
```

Default seeded login: `test@example.com` (see `database/seeders/DatabaseSeeder.php` — Jetstream's factory sets a password you'll need to check/reset via `password.php` config or `php artisan tinker`).

## Testing

```bash
php artisan test
```

Not run in this environment — see the status note above.
